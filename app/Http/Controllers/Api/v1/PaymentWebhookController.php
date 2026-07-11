<?php

namespace App\Http\Controllers\Api\v1;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Payment;
use App\Models\Refund;
use App\Services\RazorpayService;
use App\Services\InventoryService;
use App\Repositories\PaymentRepositoryInterface;
use App\Events\WebhookReceivedEvent;
use App\Events\PaymentVerified;
use App\Events\PaymentFailedEvent;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Exception;

class PaymentWebhookController extends Controller
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
     * Handle incoming Razorpay Webhook.
     */
    public function handleWebhook(Request $request): JsonResponse
    {
        $signature = $request->header('X-Razorpay-Signature');
        $payload = $request->getContent();

        if (empty($signature)) {
            Log::warning("Webhook Failed: Missing signature header.");
            return response()->json(['message' => 'Missing signature'], 400);
        }

        // 1. Verify webhook signature
        $isValid = $this->razorpayService->verifyWebhookSignature($payload, $signature);
        if (!$isValid) {
            Log::warning("Webhook Failed: Signature mismatch.");
            return response()->json(['message' => 'Invalid signature'], 400);
        }

        Log::info("Webhook Verified: Signature matched.");

        $data = $request->all();
        $event = $data['event'] ?? '';

        // Dispatch WebhookReceivedEvent
        event(new WebhookReceivedEvent($event, $data));

        try {
            switch ($event) {
                case 'order.paid':
                case 'payment.captured':
                    return $this->handlePaymentSuccess($data);

                case 'payment.authorized':
                    return $this->handlePaymentAuthorized($data);

                case 'payment.failed':
                    return $this->handlePaymentFailure($data);

                case 'refund.processed':
                    return $this->handleRefundProcessed($data);

                default:
                    Log::info("Unhandled Razorpay webhook event: {$event}");
                    return response()->json(['message' => 'Event unhandled']);
            }
        } catch (Exception $e) {
            Log::error("Error processing Razorpay webhook event {$event}: " . $e->getMessage());
            return response()->json(['message' => 'Internal server error'], 500);
        }
    }

    /**
     * Handle order.paid or payment.captured webhook event.
     */
    protected function handlePaymentSuccess(array $data): JsonResponse
    {
        $orderPayload = $data['payload']['order']['entity'] ?? null;
        $paymentPayload = $data['payload']['payment']['entity'] ?? null;

        if (!$orderPayload || !$paymentPayload) {
            return response()->json(['message' => 'Malformed payload data'], 400);
        }

        $razorpayOrderId = $orderPayload['id'];
        $razorpayPaymentId = $paymentPayload['id'];
        $receipt = $orderPayload['receipt'];
        $method = $paymentPayload['method'] ?? 'online';

        // Find order by receipt number (VIBE-...) or by gateway_order_id in payments table
        $order = Order::where('order_number', $receipt)->first();
        if (!$order) {
            $paymentRecord = $this->paymentRepository->findByGatewayOrderId($razorpayOrderId);
            if ($paymentRecord) {
                $order = Order::find($paymentRecord->order_id);
            }
        }

        if (!$order) {
            Log::warning("Order not found for Razorpay webhook: Receipt {$receipt}, Razorpay Order {$razorpayOrderId}");
            return response()->json(['message' => 'Order not found'], 404);
        }

        // Idempotency check: If order is already paid, do nothing (Ignore duplicate webhook deliveries)
        if ($order->payment_status === 'paid') {
            Log::info("Order #{$order->order_number} is already marked as Paid. Skipping webhook handler.");
            return response()->json(['message' => 'Payment already processed']);
        }

        DB::beginTransaction();
        try {
            // lock order for update
            $order = Order::where('id', $order->id)->lockForUpdate()->firstOrFail();

            if ($order->payment_status === 'paid') {
                DB::commit();
                return response()->json(['message' => 'Payment already processed']);
            }

            // Update order status
            $order->payment_status = 'paid';
            $order->status = 'processing';
            $order->payment_gateway = 'razorpay';
            $order->razorpay_order_id = $razorpayOrderId;
            $order->razorpay_payment_id = $razorpayPaymentId;
            $order->payment_response = $data;
            $order->paid_at = now();
            $order->save();

            // Update payment record using repository
            $payment = $this->paymentRepository->updateOrCreatePayment(
                [
                    'order_id' => $order->id,
                    'gateway_order_id' => $razorpayOrderId,
                ],
                [
                    'gateway' => 'razorpay',
                    'gateway_payment_id' => $razorpayPaymentId,
                    'amount' => $order->grand_total,
                    'currency' => $order->currency ?? 'INR',
                    'status' => 'captured',
                    'paid_at' => now(),
                    'gateway_response' => $data,
                    'method' => $method,
                ]
            );

            // Commit stock reservation
            foreach ($order->items as $item) {
                if ($item->product_variant_id) {
                    $this->inventoryService->commitReservedStock(
                        $item->product_variant_id,
                        $item->quantity,
                        $order->id,
                        $order->user_id
                    );
                }
            }

            DB::commit();
            Log::info("Webhook successfully processed order.paid for Order #{$order->order_number}");

            // Dispatch PaymentVerified event (which writes the "Payment Verified" log)
            event(new PaymentVerified($order, $payment));

            return response()->json(['message' => 'Payment processed successfully']);

        } catch (Exception $e) {
            DB::rollBack();
            Log::error("Failed to commit webhook payment for Order #{$order->order_number}: " . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Handle payment.authorized webhook event.
     */
    protected function handlePaymentAuthorized(array $data): JsonResponse
    {
        $paymentPayload = $data['payload']['payment']['entity'] ?? null;
        if (!$paymentPayload) {
            return response()->json(['message' => 'Malformed payment payload'], 400);
        }

        $razorpayOrderId = $paymentPayload['order_id'] ?? null;
        $razorpayPaymentId = $paymentPayload['id'];

        if (!$razorpayOrderId) {
            return response()->json(['message' => 'No order ID linked to payment'], 400);
        }

        $paymentRecord = $this->paymentRepository->findByGatewayOrderId($razorpayOrderId);
        if (!$paymentRecord) {
            return response()->json(['message' => 'Payment intent not found'], 404);
        }

        $order = Order::find($paymentRecord->order_id);
        if (!$order) {
            return response()->json(['message' => 'Order not found'], 404);
        }

        DB::beginTransaction();
        try {
            $order = Order::where('id', $order->id)->lockForUpdate()->firstOrFail();

            // Update payment record to authorized if not already captured/paid
            if ($paymentRecord->status !== 'captured') {
                $paymentRecord = $this->paymentRepository->updateOrCreatePayment(
                    [
                        'id' => $paymentRecord->id,
                    ],
                    [
                        'status' => 'authorized',
                        'gateway_payment_id' => $razorpayPaymentId,
                        'gateway_response' => $data,
                    ]
                );
            }

            DB::commit();
            Log::info("Webhook successfully processed payment.authorized for Order #{$order->order_number}");

            return response()->json(['message' => 'Payment authorized status captured']);
        } catch (Exception $e) {
            DB::rollBack();
            Log::error("Failed to process payment.authorized webhook for Order #{$order->order_number}: " . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Handle payment.failed webhook event.
     */
    protected function handlePaymentFailure(array $data): JsonResponse
    {
        $paymentPayload = $data['payload']['payment']['entity'] ?? null;
        if (!$paymentPayload) {
            return response()->json(['message' => 'Malformed payment payload'], 400);
        }

        $razorpayOrderId = $paymentPayload['order_id'] ?? null;
        $failureReason = $paymentPayload['error_description'] ?? 'Payment failed';

        if (!$razorpayOrderId) {
            return response()->json(['message' => 'No order ID linked to payment'], 400);
        }

        // Find payment record
        $paymentRecord = $this->paymentRepository->findByGatewayOrderId($razorpayOrderId);
        if (!$paymentRecord) {
            return response()->json(['message' => 'Payment intent not found'], 404);
        }

        $order = Order::find($paymentRecord->order_id);
        if (!$order) {
            return response()->json(['message' => 'Order not found'], 404);
        }

        // If order is already paid/failed, do not mark it failed again
        if ($order->payment_status === 'paid' || $order->payment_status === 'failed') {
            return response()->json(['message' => 'Order already processed, ignoring failure webhook']);
        }

        DB::beginTransaction();
        try {
            $order = Order::where('id', $order->id)->lockForUpdate()->firstOrFail();

            if ($order->payment_status === 'paid' || $order->payment_status === 'failed') {
                DB::commit();
                return response()->json(['message' => 'Order already processed, ignoring failure webhook']);
            }

            $order->payment_status = 'failed';
            $order->status = 'cancelled';
            $order->cancellation_reason = $failureReason;
            $order->cancelled_at = now();
            $order->save();

            // Update payment record status
            $paymentRecord->status = 'failed';
            $paymentRecord->failure_reason = $failureReason;
            $paymentRecord->gateway_response = $data;
            $paymentRecord->save();

            // Release stock reservation
            foreach ($order->items as $item) {
                if ($item->product_variant_id) {
                    $this->inventoryService->releaseReservedStock(
                        $item->product_variant_id,
                        $item->quantity,
                        $order->id,
                        $order->user_id
                    );
                }
            }

            // Release coupon usage if any
            $couponUsage = \App\Models\CouponUsage::where('order_id', $order->id)->first();
            if ($couponUsage) {
                $coupon = \App\Models\Coupon::find($couponUsage->coupon_id);
                if ($coupon) {
                    $coupon->decrement('times_used');
                }
                $couponUsage->delete();
            }

            DB::commit();

            // Dispatch PaymentFailedEvent (which logs "Payment Failed")
            event(new PaymentFailedEvent($order, $paymentRecord, $failureReason));

            return response()->json(['message' => 'Failure status captured']);

        } catch (Exception $e) {
            DB::rollBack();
            Log::error("Failed to process payment failure webhook for Order #{$order->order_number}: " . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Handle refund.processed webhook event.
     */
    protected function handleRefundProcessed(array $data): JsonResponse
    {
        $refundPayload = $data['payload']['refund']['entity'] ?? null;
        $paymentPayload = $data['payload']['payment']['entity'] ?? null;

        if (!$refundPayload || !$paymentPayload) {
            return response()->json(['message' => 'Malformed refund payload'], 400);
        }

        $razorpayPaymentId = $paymentPayload['id'];
        $razorpayRefundId = $refundPayload['id'];
        $refundAmount = $refundPayload['amount'] / 100; // paise to INR
        $reason = $refundPayload['notes']['reason'] ?? $refundPayload['reason'] ?? 'Refund processed via Razorpay Webhook';

        // Find the payment record
        $payment = $this->paymentRepository->findByGatewayPaymentId($razorpayPaymentId);
        if (!$payment) {
            Log::warning("Payment not found for Razorpay refund webhook: Payment {$razorpayPaymentId}");
            return response()->json(['message' => 'Payment not found'], 404);
        }

        $order = $payment->order;
        if (!$order) {
            Log::warning("Order not found for Payment {$payment->id}");
            return response()->json(['message' => 'Order not found'], 404);
        }

        DB::beginTransaction();
        try {
            $order = Order::where('id', $order->id)->lockForUpdate()->firstOrFail();

            // Update order and payment status
            $order->payment_status = 'refunded';
            $order->status = 'refunded';
            $order->save();

            $payment->status = 'refunded';
            $payment->save();

            // Create or update refund record
            Refund::updateOrCreate(
                [
                    'gateway_refund_id' => $razorpayRefundId,
                ],
                [
                    'uuid' => (string) \Illuminate\Support\Str::uuid(),
                    'payment_id' => $payment->id,
                    'order_id' => $order->id,
                    'amount' => $refundAmount,
                    'reason' => $reason,
                    'status' => 'processed',
                    'processed_at' => now(),
                    'gateway_response' => $data,
                ]
            );

            DB::commit();

            Log::info("Refund: Refund successfully processed for Order #{$order->order_number}, Amount: ₹{$refundAmount}");

            return response()->json(['message' => 'Refund processed successfully']);
        } catch (Exception $e) {
            DB::rollBack();
            Log::error("Failed to process refund webhook for Order #{$order->order_number}: " . $e->getMessage());
            throw $e;
        }
    }
}
