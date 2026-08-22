<?php

namespace App\Services;

use App\Models\Order;
use App\Models\User;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Exception;

class CashfreePaymentService
{
    protected string $appId;
    protected string $secretKey;
    protected string $environment;
    protected string $apiVersion;
    protected string $baseUrl;

    public function __construct()
    {
        $this->appId = (string) (config('services.cashfree.app_id') ?? env('CASHFREE_APP_ID', ''));
        $this->secretKey = (string) (config('services.cashfree.secret_key') ?? env('CASHFREE_SECRET_KEY', ''));
        $this->environment = strtolower((string) (config('services.cashfree.environment') ?? env('CASHFREE_ENVIRONMENT', 'sandbox')));
        $this->apiVersion = (string) (config('services.cashfree.api_version') ?? env('CASHFREE_API_VERSION', '2023-08-01'));

        $this->baseUrl = $this->environment === 'production'
            ? 'https://api.cashfree.com/pg'
            : 'https://sandbox.cashfree.com/pg';
    }

    /**
     * Get environment name.
     */
    public function getEnvironment(): string
    {
        return $this->environment;
    }

    /**
     * Get Cashfree App ID.
     */
    public function getAppId(): string
    {
        return $this->appId;
    }

    /**
     * Get API base URL.
     */
    public function getBaseUrl(): string
    {
        return $this->baseUrl;
    }

    /**
     * Create a Cashfree Order and generate a Payment Session ID.
     *
     * @param string $orderNumber
     * @param float $amount
     * @param array $customerDetails
     * @param array $options
     * @return array
     * @throws Exception
     */
    public function createCashfreeOrder(string $orderNumber, float $amount, array $customerDetails = [], array $options = []): array
    {
        if (empty($this->appId) || empty($this->secretKey)) {
            Log::error("Cashfree credentials missing in configuration.");
            throw new Exception("Cashfree payment gateway credentials are not configured.");
        }

        $rawPhone = $customerDetails['phone'] ?? '9999999999';
        $cleanPhone = preg_replace('/[^0-9]/', '', $rawPhone);
        if (strlen($cleanPhone) > 10) {
            $cleanPhone = substr($cleanPhone, -10);
        }
        if (strlen($cleanPhone) < 10) {
            $cleanPhone = str_pad($cleanPhone, 10, '9', STR_PAD_LEFT);
        }

        $customerId = !empty($customerDetails['customer_id']) 
            ? (string) $customerDetails['customer_id'] 
            : 'cust_' . substr(md5($cleanPhone . ($customerDetails['email'] ?? '')), 0, 16);

        $customerName = trim($customerDetails['name'] ?? 'Guest Customer');
        if (empty($customerName)) {
            $customerName = 'Guest Customer';
        }

        $customerEmail = trim($customerDetails['email'] ?? 'customer@mayasree.com');
        if (empty($customerEmail) || !filter_var($customerEmail, FILTER_VALIDATE_EMAIL)) {
            $customerEmail = 'customer@mayasree.com';
        }

        $payload = [
            'order_id' => $orderNumber,
            'order_amount' => round((float) $amount, 2),
            'order_currency' => 'INR',
            'customer_details' => [
                'customer_id' => $customerId,
                'customer_email' => $customerEmail,
                'customer_phone' => $cleanPhone,
                'customer_name' => $customerName,
            ],
            'order_meta' => [
                'return_url' => $options['return_url'] ?? url('/checkout?order_id=' . $orderNumber),
                'notify_url' => $options['notify_url'] ?? url('/api/payment/cashfree/webhook'),
            ],
            'order_note' => $options['order_note'] ?? ("Order #" . $orderNumber),
        ];

        try {
            $response = Http::withHeaders([
                'x-client-id' => $this->appId,
                'x-client-secret' => $this->secretKey,
                'x-api-version' => $this->apiVersion,
                'Content-Type' => 'application/json',
                'Accept' => 'application/json',
            ])->timeout(15)->post("{$this->baseUrl}/orders", $payload);

            if ($response->failed()) {
                $errorBody = $response->json();
                $errorMessage = $errorBody['message'] ?? $response->reason();
                Log::error("Cashfree Order creation failed: " . json_encode($errorBody));
                throw new Exception("Cashfree order creation failed: {$errorMessage}");
            }

            $data = $response->json();

            Log::info("Cashfree Order created successfully for #{$orderNumber}. CF Order ID: " . ($data['cf_order_id'] ?? 'N/A'));

            return $data;

        } catch (Exception $e) {
            Log::error("Cashfree API Exception on createOrder for #{$orderNumber}: " . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Fetch payments for an order from Cashfree.
     *
     * @param string $orderId
     * @return array
     * @throws Exception
     */
    public function getOrderPayments(string $orderId): array
    {
        if (empty($this->appId) || empty($this->secretKey)) {
            Log::error("Cashfree credentials missing in configuration.");
            throw new Exception("Cashfree payment gateway credentials are not configured.");
        }

        try {
            $response = Http::withHeaders([
                'x-client-id' => $this->appId,
                'x-client-secret' => $this->secretKey,
                'x-api-version' => $this->apiVersion,
                'Accept' => 'application/json',
            ])->timeout(15)->get("{$this->baseUrl}/orders/{$orderId}/payments");

            if ($response->failed()) {
                $errorBody = $response->json();
                $errorMessage = $errorBody['message'] ?? $response->reason();
                Log::error("Cashfree getOrderPayments failed for order {$orderId}: " . json_encode($errorBody));
                throw new Exception("Cashfree verification query failed: {$errorMessage}");
            }

            return $response->json() ?? [];

        } catch (Exception $e) {
            Log::error("Cashfree API Exception on getOrderPayments for order {$orderId}: " . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Fetch order details directly from Cashfree.
     *
     * @param string $orderId
     * @return array
     * @throws Exception
     */
    public function getOrderDetails(string $orderId): array
    {
        if (empty($this->appId) || empty($this->secretKey)) {
            Log::error("Cashfree credentials missing in configuration.");
            throw new Exception("Cashfree payment gateway credentials are not configured.");
        }

        try {
            $response = Http::withHeaders([
                'x-client-id' => $this->appId,
                'x-client-secret' => $this->secretKey,
                'x-api-version' => $this->apiVersion,
                'Accept' => 'application/json',
            ])->timeout(15)->get("{$this->baseUrl}/orders/{$orderId}");

            if ($response->failed()) {
                $errorBody = $response->json();
                $errorMessage = $errorBody['message'] ?? $response->reason();
                Log::error("Cashfree getOrderDetails failed for order {$orderId}: " . json_encode($errorBody));
                throw new Exception("Cashfree order fetch failed: {$errorMessage}");
            }

            return $response->json() ?? [];

        } catch (Exception $e) {
            Log::error("Cashfree API Exception on getOrderDetails for order {$orderId}: " . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Verify Webhook Signature according to Cashfree PG standards.
     *
     * Signature = Base64_Encode(HMAC_SHA256(timestamp + rawBody, secretKey))
     *
     * @param string $rawPayload
     * @param string|null $signature
     * @param string|null $timestamp
     * @return bool
     */
    public function verifyWebhookSignature(string $rawPayload, ?string $signature, ?string $timestamp): bool
    {
        if (empty($this->secretKey)) {
            Log::error("Cashfree secret key is missing for webhook signature verification.");
            return false;
        }

        if (empty($signature) || empty($timestamp)) {
            Log::warning("Cashfree webhook missing signature or timestamp header.");
            return false;
        }

        $dataToSign = $timestamp . $rawPayload;
        $expectedSignature = base64_encode(hash_hmac('sha256', $dataToSign, $this->secretKey, true));

        return hash_equals($expectedSignature, $signature);
    }
}
