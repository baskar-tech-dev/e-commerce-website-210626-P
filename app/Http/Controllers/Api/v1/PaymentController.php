<?php

namespace App\Http\Controllers\Api\v1;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Payment;
use App\Services\CashfreePaymentService;
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
    protected CashfreePaymentService $cashfreeService;
    protected InventoryService $inventoryService;
    protected PaymentRepositoryInterface $paymentRepository;

    public function __construct(
        CashfreePaymentService $cashfreeService,
        InventoryService $inventoryService,
        PaymentRepositoryInterface $paymentRepository
    ) {
        $this->cashfreeService = $cashfreeService;
        $this->inventoryService = $inventoryService;
        $this->paymentRepository = $paymentRepository;
    }

    /**
     * Create a Cashfree Order & Payment Session for an existing unpaid order.
     */
    public function createOrder(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'order_id' => 'required|exists:orders,id',
        ]);

        $user = auth('sanctum')->user() ?? auth()->user();
        $userId = $user?->id;
        $orderQuery = Order::where('id', $validated['order_id']);
        if ($userId) {
            $orderQuery->where('user_id', $userId);
        }
        $order = $orderQuery->firstOrFail();

        if ($order->payment_status === 'paid') {
            return response()->json([
                'success' => false,
                'message' => 'This order is already paid.',
            ], 422);
        }

        try {
            $customerName = trim($order->shipping_first_name . ' ' . $order->shipping_last_name);
            $customerEmail = $user?->email ?? ($order->user?->email ?? 'customer@mayasree.com');
            $customerPhone = $order->shipping_phone;

            // Generate Cashfree Order & Session
            $cfOrder = $this->cashfreeService->createCashfreeOrder(
                $order->order_number,
                (float) $order->grand_total,
                [
                    'customer_id' => 'cust_' . ($userId ?: md5($customerPhone . $customerEmail)),
                    'name' => $customerName,
                    'email' => $customerEmail,
                    'phone' => $customerPhone,
                ],
                [
                    'return_url' => url('/checkout?order_id=' . $order->order_number),
                    'notify_url' => url('/api/payment/cashfree/webhook'),
                    'order_note' => "Order #{$order->order_number} at Maya Sree Fashion",
                ]
            );

            // Record the payment intent in database (Order table)
            $order->payment_gateway = 'cashfree';
            $order->gateway_order_id = $cfOrder['order_id'] ?? $order->order_number;
            $order->save();

            // Record the payment intent in database (Payment table)
            $payment = $this->paymentRepository->updateOrCreatePayment(
                [
                    'order_id' => $order->id,
                    'gateway' => 'cashfree',
                    'status' => 'pending',
                ],
                [
                    'uuid' => (string) Str::uuid(),
                    'gateway_order_id' => $cfOrder['order_id'] ?? $order->order_number,
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
                    'payment_session_id' => $cfOrder['payment_session_id'] ?? null,
                    'cf_order_id' => $cfOrder['cf_order_id'] ?? null,
                    'order_id' => $cfOrder['order_id'] ?? $order->order_number,
                    'order_number' => $order->order_number,
                    'amount' => (float) ($cfOrder['order_amount'] ?? $order->grand_total),
                    'currency' => $cfOrder['order_currency'] ?? 'INR',
                    'environment' => $this->cashfreeService->getEnvironment(),
                    'customer' => [
                        'name' => $customerName,
                        'email' => $customerEmail,
                        'phone' => $customerPhone,
                    ]
                ]
            ]);

        } catch (Exception $e) {
            Log::error("Failed to initiate Cashfree payment for Order #{$order->order_number}: " . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to initiate Cashfree payment: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Server-side payment status verification with Cashfree.
     */
    public function verify(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'order_id' => 'required|exists:orders,id',
            'cashfree_order_id' => 'nullable|string',
        ]);

        $user = auth('sanctum')->user() ?? auth()->user();
        $userId = $user?->id;

        DB::beginTransaction();
        try {
            // Lock order for update to prevent race conditions & replay attacks
            $orderQuery = Order::where('id', $validated['order_id']);
            if ($userId) {
                $orderQuery->where('user_id', $userId);
            }
            $order = $orderQuery->lockForUpdate()->firstOrFail();

            // Check if already processed (Idempotency)
            if ($order->payment_status === 'paid') {
                DB::commit();
                return response()->json([
                    'success' => true,
                    'message' => 'Payment already verified.',
                ]);
            }

            $orderIdentifier = $validated['cashfree_order_id'] ?? ($order->gateway_order_id ?? $order->order_number);

            // 1. Query Cashfree API to verify actual transaction status on the server
            $payments = $this->cashfreeService->getOrderPayments($orderIdentifier);
            
            $successfulPayment = null;
            $failedReason = null;

            if (is_array($payments)) {
                foreach ($payments as $paymentAttempt) {
                    $status = strtoupper($paymentAttempt['payment_status'] ?? '');
                    if ($status === 'SUCCESS') {
                        $successfulPayment = $paymentAttempt;
                        break;
                    } elseif (in_array($status, ['FAILED', 'USER_DROPPED', 'CANCELLED'])) {
                        $failedReason = $paymentAttempt['payment_message'] ?? ($paymentAttempt['error_details']['error_description'] ?? 'Payment attempt failed.');
                    }
                }
            }

            if (!$successfulPayment) {
                $payment = $this->paymentRepository->findByGatewayOrderId($orderIdentifier);
                if ($payment) {
                    event(new PaymentFailedEvent($order, $payment, $failedReason ?? 'Payment not confirmed by Cashfree.'));
                }
                DB::rollBack();
                Log::warning("Cashfree payment verification failed for Order #{$order->order_number}. Reason: " . ($failedReason ?? 'No successful transaction recorded.'));
                return response()->json([
                    'success' => false,
                    'message' => $failedReason ?? 'Payment verification failed. No successful transaction confirmed.',
                ], 400);
            }

            // Amount mismatch validation
            $paidAmount = (float) ($successfulPayment['payment_amount'] ?? 0);
            $expectedAmount = (float) $order->grand_total;
            if (abs($paidAmount - $expectedAmount) > 0.05) {
                DB::rollBack();
                Log::error("Payment amount mismatch for Order #{$order->order_number}. Expected: {$expectedAmount}, Received: {$paidAmount}");
                return response()->json([
                    'success' => false,
                    'message' => 'Payment amount mismatch detected. Please contact support.',
                ], 400);
            }

            $cfPaymentId = (string) ($successfulPayment['cf_payment_id'] ?? '');
            $paymentMethod = $successfulPayment['payment_group'] ?? 'online';

            // 2. Update Order Statuses & Gateway fields
            $order->payment_status = 'paid';
            $order->status = 'processing';
            $order->payment_gateway = 'cashfree';
            $order->gateway_order_id = $orderIdentifier;
            $order->gateway_payment_id = $cfPaymentId;
            $order->payment_response = $successfulPayment;
            $order->paid_at = now();
            $order->save();

            // 3. Update Payment Table
            $payment = $this->paymentRepository->updateOrCreatePayment(
                [
                    'order_id' => $order->id,
                    'gateway_order_id' => $orderIdentifier,
                ],
                [
                    'gateway' => 'cashfree',
                    'gateway_payment_id' => $cfPaymentId,
                    'amount' => $order->grand_total,
                    'currency' => $order->currency ?? 'INR',
                    'status' => 'captured',
                    'paid_at' => now(),
                    'gateway_response' => $successfulPayment,
                    'method' => $paymentMethod,
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

            // Dispatch PaymentVerified event
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
                'message' => 'Error verifying and capturing payment: ' . $e->getMessage(),
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

        $user = auth('sanctum')->user() ?? auth()->user();
        $userId = $user?->id;
        
        DB::beginTransaction();
        try {
            $orderQuery = Order::where('id', $validated['order_id']);
            if ($userId) {
                $orderQuery->where('user_id', $userId);
            }
            $order = $orderQuery->lockForUpdate()->firstOrFail();

            if ($order->payment_status === 'paid') {
                DB::commit();
                return response()->json([
                    'success' => false,
                    'message' => 'Cannot cancel a paid order.',
                ], 422);
            }

            $reason = $validated['reason'] ?? 'Payment dropped or cancelled by customer';

            // Update statuses
            $order->payment_status = 'failed';
            $order->status = 'cancelled';
            $order->cancellation_reason = $reason;
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
                    'gateway' => 'cashfree',
                ],
                [
                    'amount' => $order->grand_total,
                    'currency' => $order->currency ?? 'INR',
                    'status' => 'failed',
                    'failure_reason' => $reason,
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

            // Dispatch PaymentFailedEvent
            event(new PaymentFailedEvent($order, $payment, $reason));

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

    /**
     * Get verified status of an order for storefront status/success confirmation.
     */
    public function status(int $orderId): JsonResponse
    {
        $user = auth('sanctum')->user() ?? auth()->user();
        $userId = $user?->id;

        $orderQuery = Order::with('payments')->where('id', $orderId);
        if ($userId) {
            $orderQuery->where('user_id', $userId);
        }
        $order = $orderQuery->first();

        if (!$order) {
            return response()->json([
                'success' => false,
                'message' => 'Order not found.',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => [
                'order_id' => $order->id,
                'order_number' => $order->order_number,
                'status' => $order->status,
                'payment_status' => $order->payment_status,
                'payment_gateway' => $order->payment_gateway,
                'grand_total' => $order->grand_total,
                'paid_at' => $order->paid_at,
            ]
        ]);
    }
}
