<?php

namespace App\Listeners;

use App\Events\PaymentStarted;
use App\Events\PaymentVerified;
use App\Events\PaymentFailedEvent;
use App\Events\WebhookReceivedEvent;
use Illuminate\Support\Facades\Log;

class LogPaymentActivity
{
    /**
     * Handle payment started event.
     */
    public function handlePaymentStarted(PaymentStarted $event): void
    {
        Log::info("Payment Started for Order #{$event->order->order_number}. local payment UUID: {$event->payment->uuid}");
    }

    /**
     * Handle payment verified event.
     */
    public function handlePaymentVerified(PaymentVerified $event): void
    {
        Log::info("Payment Verified for Order #{$event->order->order_number}. Gateway Payment ID: {$event->payment->gateway_payment_id}");
    }

    /**
     * Handle payment failed event.
     */
    public function handlePaymentFailed(PaymentFailedEvent $event): void
    {
        Log::warning("Payment Failed for Order #{$event->order->order_number}. Reason: {$event->reason}");
    }

    /**
     * Handle webhook received event.
     */
    public function handleWebhookReceived(WebhookReceivedEvent $event): void
    {
        Log::info("Webhook Received: {$event->event}");
    }

    /**
     * Register the listeners for the subscriber.
     *
     * @param \Illuminate\Events\Dispatcher $events
     * @return array
     */
    public function subscribe($events): array
    {
        return [
            PaymentStarted::class => 'handlePaymentStarted',
            PaymentVerified::class => 'handlePaymentVerified',
            PaymentFailedEvent::class => 'handlePaymentFailed',
            WebhookReceivedEvent::class => 'handleWebhookReceived',
        ];
    }
}
