<?php

namespace App\Services;

use App\Models\Order;
use App\Models\User;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;
use Exception;

class CashfreeService
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
     * Get environment name ('sandbox' or 'production').
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
     * Get API Version.
     */
    public function getApiVersion(): string
    {
        return $this->apiVersion;
    }

    /**
     * Check if Cashfree credentials are configured.
     */
    public function isConfigured(): bool
    {
        return !empty($this->appId) && !empty($this->secretKey);
    }

    /**
     * Common HTTP client headers for Cashfree API requests.
     */
    protected function getHeaders(): array
    {
        return [
            'x-client-id' => $this->appId,
            'x-client-secret' => $this->secretKey,
            'x-api-version' => $this->apiVersion,
            'Content-Type' => 'application/json',
            'Accept' => 'application/json',
        ];
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
        if (!$this->isConfigured()) {
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
            $response = Http::withHeaders($this->getHeaders())
                ->timeout(15)
                ->post("{$this->baseUrl}/orders", $payload);

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
        if (!$this->isConfigured()) {
            Log::error("Cashfree credentials missing in configuration.");
            throw new Exception("Cashfree payment gateway credentials are not configured.");
        }

        try {
            $response = Http::withHeaders($this->getHeaders())
                ->timeout(15)
                ->get("{$this->baseUrl}/orders/{$orderId}/payments");

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
        if (!$this->isConfigured()) {
            Log::error("Cashfree credentials missing in configuration.");
            throw new Exception("Cashfree payment gateway credentials are not configured.");
        }

        try {
            $response = Http::withHeaders($this->getHeaders())
                ->timeout(15)
                ->get("{$this->baseUrl}/orders/{$orderId}");

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
     * Fetch settlements from Cashfree Reconciliation / Settlements API.
     *
     * @param array $filters ['start_date' => '', 'end_date' => '', 'settlement_id' => '', 'utr' => '']
     * @param string|null $cursor
    /**
     * Test Cashfree API connection & authentication.
     */
    public function testConnection(): array
    {
        if (!$this->isConfigured()) {
            return [
                'success' => false,
                'environment' => $this->environment,
                'message' => 'Cashfree credentials (CASHFREE_APP_ID or CASHFREE_SECRET_KEY) are missing in .env.',
            ];
        }

        try {
            $startDate = date('Y-m-d\TH:i:s\Z', strtotime('-30 days'));
            $endDate = date('Y-m-d\TH:i:s\Z');

            $payload = [
                'pagination' => [
                    'limit' => 10,
                ],
                'filters' => [
                    'start_date' => $startDate,
                    'end_date' => $endDate,
                ],
            ];

            $response = Http::withHeaders($this->getHeaders())
                ->timeout(12)
                ->post("{$this->baseUrl}/settlement/recon", $payload);

            if ($response->successful()) {
                $data = $response->json();
                $count = count($data['data'] ?? ($data['settlements'] ?? []));
                return [
                    'success' => true,
                    'environment' => $this->environment,
                    'message' => 'Cashfree Payment Gateway & Settlements API connected successfully.',
                    'settlement_records_count' => $count,
                ];
            }

            $body = $response->json();
            return [
                'success' => false,
                'environment' => $this->environment,
                'status_code' => $response->status(),
                'error' => $body['message'] ?? ($response->reason() ?: 'Unknown response from Cashfree'),
            ];
        } catch (\Throwable $e) {
            return [
                'success' => false,
                'environment' => $this->environment,
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Fetch settlements from Cashfree Reconciliation / Settlements API.
     *
     * @param array $filters ['start_date' => '', 'end_date' => '', 'settlement_id' => '', 'utr' => '']
     * @param string|null $cursor
     * @param int $limit
     * @return array
     * @throws Exception
     */
    public function getSettlements(array $filters = [], ?string $cursor = null, int $limit = 20): array
    {
        if (!$this->isConfigured()) {
            Log::warning("Cashfree credentials missing for getSettlements.");
            throw new Exception("Cashfree credentials are not configured.");
        }

        $cacheKey = 'cf_settlements_' . md5(json_encode($filters) . '_' . ($cursor ?? 'root') . '_' . $limit);

        return Cache::remember($cacheKey, 120, function () use ($filters, $cursor, $limit) {
            try {
                // Prepare filters (Cashfree accepts start_date / end_date or start_date_initiated_on)
                $startDate = !empty($filters['start_date']) 
                    ? date('Y-m-d\TH:i:s\Z', strtotime($filters['start_date'])) 
                    : date('Y-m-d\TH:i:s\Z', strtotime('-30 days'));

                $endDate = !empty($filters['end_date']) 
                    ? date('Y-m-d\TH:i:s\Z', strtotime($filters['end_date'] . ' 23:59:59')) 
                    : date('Y-m-d\TH:i:s\Z');

                $reconFilters = [
                    'start_date' => $startDate,
                    'end_date' => $endDate,
                ];

                if (!empty($filters['settlement_id'])) {
                    $reconFilters['settlement_id'] = (string) $filters['settlement_id'];
                    $reconFilters['cf_settlement_ids'] = [(string) $filters['settlement_id']];
                }
                if (!empty($filters['utr'])) {
                    $reconFilters['settlement_utr'] = (string) $filters['utr'];
                    $reconFilters['settlement_utrs'] = [(string) $filters['utr']];
                }

                $payload = [
                    'pagination' => [
                        'limit' => min(max($limit, 10), 100),
                        'cursor' => $cursor ?: null,
                    ],
                    'filters' => $reconFilters,
                ];

                // Attempt Settlement Reconciliation API (POST /settlement/recon)
                $response = Http::withHeaders($this->getHeaders())
                    ->timeout(15)
                    ->post("{$this->baseUrl}/settlement/recon", $payload);

                if ($response->successful()) {
                    return $response->json() ?? [];
                }

                // If recon endpoint is not supported or returned 404/405, fallback to GET /settlements
                if ($response->status() === 404 || $response->status() === 405) {
                    $queryParams = array_filter([
                        'limit' => $limit,
                        'cursor' => $cursor,
                        'start_date' => $filters['start_date'] ?? null,
                        'end_date' => $filters['end_date'] ?? null,
                    ]);

                    $getResp = Http::withHeaders($this->getHeaders())
                        ->timeout(15)
                        ->get("{$this->baseUrl}/settlements", $queryParams);

                    if ($getResp->successful()) {
                        return $getResp->json() ?? [];
                    }
                }

                $errorBody = $response->json();
                $errorMessage = $errorBody['message'] ?? $response->reason();
                Log::error("Cashfree getSettlements API failed: " . json_encode($errorBody));
                throw new Exception("Cashfree settlements query failed: {$errorMessage}");

            } catch (Exception $e) {
                Log::error("Cashfree API Exception on getSettlements: " . $e->getMessage());
                throw $e;
            }
        });
    }

    /**
     * Fetch settlements for a specific order from Cashfree.
     *
     * @param string $orderId
     * @return array
     * @throws Exception
     */
    public function getOrderSettlements(string $orderId): array
    {
        if (!$this->isConfigured()) {
            Log::error("Cashfree credentials missing in configuration.");
            throw new Exception("Cashfree payment gateway credentials are not configured.");
        }

        try {
            $response = Http::withHeaders($this->getHeaders())
                ->timeout(15)
                ->get("{$this->baseUrl}/orders/{$orderId}/settlements");

            if ($response->failed()) {
                $errorBody = $response->json();
                $errorMessage = $errorBody['message'] ?? $response->reason();
                Log::error("Cashfree getOrderSettlements failed for order {$orderId}: " . json_encode($errorBody));
                throw new Exception("Cashfree order settlements query failed: {$errorMessage}");
            }

            return $response->json() ?? [];

        } catch (Exception $e) {
            Log::error("Cashfree API Exception on getOrderSettlements for order {$orderId}: " . $e->getMessage());
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
