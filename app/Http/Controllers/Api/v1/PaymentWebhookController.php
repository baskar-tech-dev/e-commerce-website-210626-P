<?php

namespace App\Http\Controllers\Api\v1;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Payment;
use App\Models\Refund;
use App\Services\CashfreePaymentService;
use App\Services\InventoryService;
use App\Repositories\PaymentRepositoryInterface;
use App\Events\WebhookReceivedEvent;
use App\Events\PaymentVerified;
use App\Events\PaymentFailedEvent;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Exception;

class PaymentWebhookController extends Controller
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
     * Handle incoming Cashfree Webhook.
     */
    public function handleWebhook(Request $request): JsonResponse
    {
        $signature = $request->header('x-webhook-signature') ?? $request->header('X-Webhook-Signature');
        $timestamp = $request->header('x-webhook-timestamp') ?? $request->header('X-Webhook-Timestamp');
        $rawPayload = $request->getContent();

        if (empty($signature) || empty($timestamp)) {
            Log::warning("Cashfree Webhook Failed: Missing signature or timestamp header.");
            return response()->json(['message' => 'Missing signature or timestamp header'], 400);
        }

        // 1. Verify Cashfree webhook signature
        $isValid = $this->cashfreeService->verifyWebhookSignature($rawPayload, $signature, $timestamp);
        if (!$isValid) {
            Log::warning("Cashfree Webhook Failed: Signature verification failed.");
            return response()->json(['message' => 'Invalid webhook signature'], 400);
        }

        Log::info("Cashfree Webhook Verified: Signature matched.");

        $data = $request->all();
        $eventType = strtoupper($data['type'] ?? ($data['event'] ?? ''));

        // Dispatch WebhookReceivedEvent
        event(new WebhookReceivedEvent($eventType, $data));

        try {
            switch ($eventType) {
                case 'PAYMENT_SUCCESS_WEBHOOK':
                case 'PAYMENT_CAPTURED':
                case 'ORDER_PAID':
                    return $this->handlePaymentSuccess($data);

                case 'PAYMENT_FAILED_WEBHOOK':
                case 'PAYMENT_USER_DROPPED_WEBHOOK':
                case 'PAYMENT_FAILED':
                    return $this->handlePaymentFailure($data);

                case 'REFUND_STATUS_WEBHOOK':
                case 'REFUND_PROCESSED':
                    return $this->handleRefundProcessed($data);

                default:
                    Log::info("Unhandled Cashfree webhook event type: {$eventType}");
                    return response()->json(['message' => 'Event unhandled']);
            }
        } catch (Exception $e) {
            Log::error("Error processing Cashfree webhook event {$eventType}: " . $e->getMessage());
            return response()->json(['message' => 'Internal server error processing webhook'], 500);
        }
    }

    /**
     * Handle PAYMENT_SUCCESS_WEBHOOK event.
     */
    protected function handlePaymentSuccess(array $data): JsonResponse
    {
        $orderData = $data['data']['order'] ?? ($data['payload']['order']['entity'] ?? null);
        $paymentData = $data['data']['payment'] ?? ($data['payload']['payment']['entity'] ?? null);

        if (!$orderData || !$paymentData) {
            return response()->json(['message' => 'Malformed webhook payload structure'], 400);
        }

        $orderNumber = $orderData['order_id'] ?? null;
        $cfPaymentId = (string) ($paymentData['cf_payment_id'] ?? ($paymentData['id'] ?? ''));
        $paymentMethod = $paymentData['payment_group'] ?? ($paymentData['method'] ?? 'online');
        $paidAmount = (float) ($paymentData['payment_amount'] ?? ($paymentData['amount'] ?? 0));

        // Locate order in database
        $order = Order::where('order_number', $orderNumber)->first();
        if (!$order) {
            $paymentRecord = $this->paymentRepository->findByGatewayOrderId($orderNumber);
            if ($paymentRecord) {
                $order = Order::find($paymentRecord->order_id);
            }
        }

        if (!$order) {
            Log::warning("Order not found for Cashfree webhook: Order ID {$orderNumber}");
            return response()->json(['message' => 'Order not found'], 404);
        }

        // Idempotency check: If order is already paid, do nothing
        if ($order->payment_status === 'paid') {
            Log::info("Order #{$order->order_number} is already marked as Paid. Skipping duplicate webhook.");
            return response()->json(['message' => 'Payment already processed']);
        }

        // Validate payment amount matches expected order amount
        if (abs($paidAmount - (float) $order->grand_total) > 0.05) {
            Log::error("Webhook payment amount mismatch for Order #{$order->order_number}. Order total: {$order->grand_total}, Paid: {$paidAmount}");
            return response()->json(['message' => 'Payment amount mismatch'], 400);
        }

        DB::beginTransaction();
        try {
            // Lock order for update
            $order = Order::where('id', $order->id)->lockForUpdate()->firstOrFail();

            if ($order->payment_status === 'paid') {
                DB::commit();
                return response()->json(['message' => 'Payment already processed']);
            }

            // Update order status
            $order->payment_status = 'paid';
            $order->status = 'processing';
            $order->payment_gateway = 'cashfree';
            $order->gateway_order_id = $orderNumber;
            $order->gateway_payment_id = $cfPaymentId;
            $order->payment_response = $data;
            $order->paid_at = now();
            $order->save();

            // Update payment record in repository
            $payment = $this->paymentRepository->updateOrCreatePayment(
                [
                    'order_id' => $order->id,
                    'gateway_order_id' => $orderNumber,
                ],
                [
                    'gateway' => 'cashfree',
                    'gateway_payment_id' => $cfPaymentId,
                    'amount' => $order->grand_total,
                    'currency' => $order->currency ?? 'INR',
                    'status' => 'captured',
                    'paid_at' => now(),
                    'gateway_response' => $data,
                    'method' => $paymentMethod,
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
            Log::info("Webhook successfully captured PAYMENT_SUCCESS for Order #{$order->order_number}");

            // Dispatch PaymentVerified event
            event(new PaymentVerified($order, $payment));

            return response()->json(['message' => 'Payment processed successfully']);

        } catch (Exception $e) {
            DB::rollBack();
            Log::error("Failed to commit webhook payment for Order #{$order->order_number}: " . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Handle PAYMENT_FAILED_WEBHOOK and PAYMENT_USER_DROPPED_WEBHOOK events.
     */
    protected function handlePaymentFailure(array $data): JsonResponse
    {
        $orderData = $data['data']['order'] ?? ($data['payload']['order']['entity'] ?? null);
        $paymentData = $data['data']['payment'] ?? ($data['payload']['payment']['entity'] ?? null);

        $orderNumber = $orderData['order_id'] ?? null;
        $failureReason = $paymentData['payment_message'] ?? ($paymentData['error_details']['error_description'] ?? 'Payment failed or dropped');

        if (!$orderNumber) {
            return response()->json(['message' => 'No order ID in webhook payload'], 400);
        }

        $order = Order::where('order_number', $orderNumber)->first();
        if (!$order) {
            $paymentRecord = $this->paymentRepository->findByGatewayOrderId($orderNumber);
            if ($paymentRecord) {
                $order = Order::find($paymentRecord->order_id);
            }
        }

        if (!$order) {
            return response()->json(['message' => 'Order not found'], 404);
        }

        // If order is already paid or failed, do not mutate state
        if ($order->payment_status === 'paid' || $order->payment_status === 'failed') {
            return response()->json(['message' => 'Order already processed, skipping failure webhook']);
        }

        DB::beginTransaction();
        try {
            $order = Order::where('id', $order->id)->lockForUpdate()->firstOrFail();

            if ($order->payment_status === 'paid' || $order->payment_status === 'failed') {
                DB::commit();
                return response()->json(['message' => 'Order already processed, skipping failure webhook']);
            }

            $order->payment_status = 'failed';
            $order->status = 'cancelled';
            $order->cancellation_reason = $failureReason;
            $order->cancelled_at = now();
            $order->save();

            // Update payment record
            $paymentRecord = $this->paymentRepository->updateOrCreatePayment(
                [
                    'order_id' => $order->id,
                    'gateway_order_id' => $orderNumber,
                ],
                [
                    'gateway' => 'cashfree',
                    'amount' => $order->grand_total,
                    'currency' => $order->currency ?? 'INR',
                    'status' => 'failed',
                    'failure_reason' => $failureReason,
                    'gateway_response' => $data,
                    'method' => $paymentData['payment_group'] ?? 'online',
                ]
            );

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

            // Dispatch PaymentFailedEvent
            event(new PaymentFailedEvent($order, $paymentRecord, $failureReason));

            return response()->json(['message' => 'Failure status captured']);

        } catch (Exception $e) {
            DB::rollBack();
            Log::error("Failed to process payment failure webhook for Order #{$order->order_number}: " . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Handle REFUND_STATUS_WEBHOOK event.
     */
    protected function handleRefundProcessed(array $data): JsonResponse
    {
        $refundData = $data['data']['refund'] ?? ($data['payload']['refund']['entity'] ?? null);
        $paymentData = $data['data']['payment'] ?? ($data['payload']['payment']['entity'] ?? null);
        $orderData = $data['data']['order'] ?? ($data['payload']['order']['entity'] ?? null);

        if (!$refundData) {
            return response()->json(['message' => 'Malformed refund webhook payload'], 400);
        }

        $cfRefundId = (string) ($refundData['cf_refund_id'] ?? ($refundData['refund_id'] ?? ''));
        $cfPaymentId = (string) ($paymentData['cf_payment_id'] ?? '');
        $refundAmount = (float) ($refundData['refund_amount'] ?? ($refundData['amount'] ?? 0));
        $reason = $refundData['refund_note'] ?? ($refundData['reason'] ?? 'Refund processed via Cashfree Webhook');

        $payment = null;
        if (!empty($cfPaymentId)) {
            $payment = $this->paymentRepository->findByGatewayPaymentId($cfPaymentId);
        }

        if (!$payment && !empty($orderData['order_id'])) {
            $payment = $this->paymentRepository->findByGatewayOrderId($orderData['order_id']);
        }

        if (!$payment) {
            Log::warning("Payment not found for Cashfree refund webhook: Payment {$cfPaymentId}");
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
                    'gateway_refund_id' => $cfRefundId,
                ],
                [
                    'uuid' => (string) Str::uuid(),
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

            Log::info("Refund: Cashfree refund successfully processed for Order #{$order->order_number}, Amount: ₹{$refundAmount}");

            return response()->json(['message' => 'Refund processed successfully']);
        } catch (Exception $e) {
            DB::rollBack();
            Log::error("Failed to process refund webhook for Order #{$order->order_number}: " . $e->getMessage());
            throw $e;
        }
    }
}
