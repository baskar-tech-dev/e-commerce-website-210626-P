<?php

namespace App\Http\Controllers\Api\v1;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Payment;
use App\Services\RazorpayService;
use App\Services\InventoryService;
use App\Repositories\PaymentRepositoryInterface;
use App\Events\PaymentStarted;
use App\Events\PaymentVerified;
use App\Events\PaymentFailedEvent;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Exception;

class PaymentController extends Controller
{
    protected $razorpayService;
    protected $inventoryService;
    protected $paymentRepository;

    public function __construct(
        RazorpayService $razorpayService,
        InventoryService $inventoryService,
        PaymentRepositoryInterface $paymentRepository
    ) {
        $this->razorpayService = $razorpayService;
        $this->inventoryService = $inventoryService;
        $this->paymentRepository = $paymentRepository;
    }

    /**
     * Create a Razorpay Order for an existing unpaid Laravel order.
     */
    public function createOrder(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'order_id' => 'required|exists:orders,id',
        ]);

        $userId = auth()->id();
        $order = Order::where('id', $validated['order_id'])
            ->where('user_id', $userId)
            ->firstOrFail();

        if ($order->payment_status === 'paid') {
            return response()->json([
                'success' => false,
                'message' => 'This order is already paid.',
            ], 422);
        }

        try {
            // Generate Razorpay Order
            $razorpayOrder = $this->razorpayService->createRazorpayOrder(
                $order->order_number,
                $order->grand_total
            );

            // Record the payment intent in database (Order table)
            $order->payment_gateway = 'razorpay';
            $order->razorpay_order_id = $razorpayOrder['id'];
            $order->save();

            // Record the payment intent in database (Payment table)
            $payment = $this->paymentRepository->updateOrCreatePayment(
                [
                    'order_id' => $order->id,
                    'gateway' => 'razorpay',
                    'status' => 'pending',
                ],
                [
                    'uuid' => (string) Str::uuid(),
                    'gateway_order_id' => $razorpayOrder['id'],
                    'method' => 'online',
                    'amount' => $order->grand_total,
                    'currency' => 'INR',
                ]
            );

            // Dispatch PaymentStarted event
            event(new PaymentStarted($order, $payment));

            return response()->json([
                'success' => true,
                'data' => [
                    'key_id' => env('RAZORPAY_KEY_ID'),
                    'razorpay_order_id' => $razorpayOrder['id'],
                    'amount' => $razorpayOrder['amount'],
                    'currency' => $razorpayOrder['currency'],
                    'order_number' => $order->order_number,
                    'customer' => [
                        'name' => trim($order->shipping_first_name . ' ' . $order->shipping_last_name),
                        'email' => auth()->user()->email ?? '',
                        'phone' => $order->shipping_phone,
                    ]
                ]
            ]);

        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to initiate Razorpay payment: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Verify payment signature from client side.
     */
    public function verify(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'order_id' => 'required|exists:orders,id',
            'razorpay_order_id' => 'required|string',
            'razorpay_payment_id' => 'required|string',
            'razorpay_signature' => 'required|string',
        ]);

        $userId = auth()->id();

        DB::beginTransaction();
        try {
            // lock order for update to prevent race conditions (prevent duplicate payments / replay attacks)
            $order = Order::where('id', $validated['order_id'])
                ->where('user_id', $userId)
                ->lockForUpdate()
                ->firstOrFail();

            // Check if already processed
            if ($order->payment_status === 'paid') {
                DB::commit();
                return response()->json([
                    'success' => true,
                    'message' => 'Payment already verified.',
                ]);
            }

            // 1. Verify Signature inside transaction
            $isValid = $this->razorpayService->verifySignature(
                $validated['razorpay_order_id'],
                $validated['razorpay_payment_id'],
                $validated['razorpay_signature']
            );

            if (!$isValid) {
                $payment = $this->paymentRepository->findByGatewayOrderId($validated['razorpay_order_id']);
                if ($payment) {
                    event(new PaymentFailedEvent($order, $payment, 'Signature verification failed.'));
                }
                DB::rollBack();
                Log::warning("Razorpay signature verification failed for order: {$order->order_number}");
                return response()->json([
                    'success' => false,
                    'message' => 'Payment signature verification failed.',
                ], 400);
            }

            // 2. Update Order Statuses & Razorpay fields
            $order->payment_status = 'paid';
            $order->status = 'processing';
            $order->payment_gateway = 'razorpay';
            $order->razorpay_order_id = $validated['razorpay_order_id'];
            $order->razorpay_payment_id = $validated['razorpay_payment_id'];
            $order->razorpay_signature = $validated['razorpay_signature'];
            $order->payment_response = $request->all();
            $order->paid_at = now();
            $order->save();

            // 3. Update Payment Table
            $payment = $this->paymentRepository->updateOrCreatePayment(
                [
                    'order_id' => $order->id,
                    'gateway_order_id' => $validated['razorpay_order_id'],
                ],
                [
                    'gateway' => 'razorpay',
                    'gateway_payment_id' => $validated['razorpay_payment_id'],
                    'gateway_signature' => $validated['razorpay_signature'],
                    'amount' => $order->grand_total,
                    'currency' => $order->currency ?? 'INR',
                    'status' => 'captured',
                    'paid_at' => now(),
                    'gateway_response' => $request->all(),
                    'method' => 'online',
                ]
            );

            // 4. Commit stock reservation
            foreach ($order->items as $item) {
                if ($item->product_variant_id) {
                    $this->inventoryService->commitReservedStock(
                        $item->product_variant_id,
                        $item->quantity,
                        $order->id,
                        $userId
                    );
                }
            }

            DB::commit();

            // Dispatch PaymentVerified event (which writes the "Payment Verified" log)
            event(new PaymentVerified($order, $payment));

            return response()->json([
                'success' => true,
                'message' => 'Payment verified and captured successfully.',
            ]);

        } catch (Exception $e) {
            DB::rollBack();
            Log::error("Failed to commit payment transaction for Order #{$order->order_number}: " . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error capturing payment: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Cancel/Fail payment endpoint to release reserved stock.
     */
    public function cancel(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'order_id' => 'required|exists:orders,id',
            'reason' => 'nullable|string',
        ]);

        $userId = auth()->id();
        
        DB::beginTransaction();
        try {
            $order = Order::where('id', $validated['order_id'])
                ->where('user_id', $userId)
                ->lockForUpdate()
                ->firstOrFail();

            if ($order->payment_status === 'paid') {
                DB::commit();
                return response()->json([
                    'success' => false,
                    'message' => 'Cannot cancel a paid order.',
                ], 422);
            }

            // Update statuses
            $order->payment_status = 'failed';
            $order->status = 'cancelled';
            $order->cancellation_reason = $validated['reason'] ?? 'Payment failed or cancelled';
            $order->cancelled_at = now();
            $order->save();

            // Release coupon usage if any
            $couponUsage = \App\Models\CouponUsage::where('order_id', $order->id)->first();
            if ($couponUsage) {
                $coupon = \App\Models\Coupon::find($couponUsage->coupon_id);
                if ($coupon) {
                    $coupon->decrement('times_used');
                }
                $couponUsage->delete();
            }

            // Update payment record
            $payment = $this->paymentRepository->updateOrCreatePayment(
                [
                    'order_id' => $order->id,
                    'gateway' => 'razorpay',
                ],
                [
                    'amount' => $order->grand_total,
                    'currency' => $order->currency ?? 'INR',
                    'status' => 'failed',
                    'failure_reason' => $validated['reason'] ?? 'Payment failed or cancelled',
                    'gateway_response' => $request->all(),
                    'method' => 'online',
                ]
            );

            // Release stock reservation
            foreach ($order->items as $item) {
                if ($item->product_variant_id) {
                    $this->inventoryService->releaseReservedStock(
                        $item->product_variant_id,
                        $item->quantity,
                        $order->id,
                        $userId
                    );
                }
            }

            DB::commit();

            // Dispatch PaymentFailedEvent (which logs "Payment Failed")
            event(new PaymentFailedEvent($order, $payment, $validated['reason'] ?? 'Payment failed or cancelled'));

            return response()->json([
                'success' => true,
                'message' => 'Payment cancelled and stock reservation released.',
            ]);

        } catch (Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Error processing cancellation: ' . $e->getMessage(),
            ], 500);
        }
    }
}
