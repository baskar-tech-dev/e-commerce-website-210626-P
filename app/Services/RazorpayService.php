<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Exception;

class RazorpayService
{
    protected $keyId;
    protected $keySecret;
    protected $webhookSecret;
    protected $baseUrl = 'https://api.razorpay.com/v1';

    public function __construct()
    {
        $this->keyId = config('services.razorpay.key_id') ?? env('RAZORPAY_KEY_ID');
        $this->keySecret = config('services.razorpay.key_secret') ?? env('RAZORPAY_KEY_SECRET');
        $this->webhookSecret = config('services.razorpay.webhook_secret') ?? env('RAZORPAY_WEBHOOK_SECRET');
    }

    /**
     * Create a Razorpay Order.
     */
    public function createRazorpayOrder(string $receiptId, float $amountInInr): array
    {
        $amountInPaise = (int) round($amountInInr * 100);

        try {
            $response = Http::withBasicAuth($this->keyId, $this->keySecret)
                ->post("{$this->baseUrl}/orders", [
                    'amount' => $amountInPaise,
                    'currency' => 'INR',
                    'receipt' => $receiptId,
                ]);

            if ($response->failed()) {
                Log::error("Razorpay order creation failed: " . $response->body());
                throw new Exception("Failed to create payment order: " . ($response->json()['error']['description'] ?? $response->reason()));
            }

            return $response->json();
        } catch (Exception $e) {
            Log::error("Razorpay order API error: " . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Verify the signature of a payment.
     */
    public function verifySignature(string $razorpayOrderId, string $razorpayPaymentId, string $razorpaySignature): bool
    {
        if (empty($this->keySecret)) {
            Log::error("Razorpay secret key is not set in env config.");
            return false;
        }
        $expectedSignature = hash_hmac('sha256', $razorpayOrderId . '|' . $razorpayPaymentId, $this->keySecret);
        return hash_equals($expectedSignature, $razorpaySignature);
    }

    /**
     * Verify Razorpay Webhook Signature.
     */
    public function verifyWebhookSignature(string $payload, string $signature): bool
    {
        if (empty($this->webhookSecret)) {
            Log::error("Razorpay webhook secret is not set in env config.");
            return false;
        }
        $expectedSignature = hash_hmac('sha256', $payload, $this->webhookSecret);
        return hash_equals($expectedSignature, $signature);
    }
}
