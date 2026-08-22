<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\Category;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Payment;
use App\Services\CashfreePaymentService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CashfreePaymentTest extends TestCase
{
    use RefreshDatabase;

    protected $customer;
    protected $category;
    protected $product;
    protected $variant;
    protected $order;

    protected function setUp(): void
    {
        parent::setUp();

        $this->customer = User::factory()->create([
            'email' => 'buyer@test.com',
            'first_name' => 'John',
            'last_name' => 'Buyer',
            'phone' => '9876543210',
        ]);

        $this->category = Category::create([
            'name' => 'Ethnic Wear',
            'slug' => 'ethnic-wear',
            'is_active' => true,
        ]);

        $this->product = Product::create([
            'category_id' => $this->category->id,
            'name' => 'Kanchipuram Silk Saree',
            'slug' => 'kanchipuram-silk-saree',
            'mrp' => 2999.00,
            'selling_price' => 1499.00,
            'is_active' => true,
        ]);

        $this->variant = ProductVariant::create([
            'product_id' => $this->product->id,
            'sku' => 'KAN-SILK-MAROON',
            'size' => 'Free Size',
            'stock_quantity' => 10,
            'reserved_quantity' => 0,
            'is_active' => true,
        ]);

        // Place a mock order
        $this->order = Order::create([
            'uuid' => 'fake-uuid-123',
            'order_number' => 'VIBE-TEST123',
            'user_id' => $this->customer->id,
            'status' => 'pending',
            'payment_status' => 'pending',
            'payment_method' => 'online',
            'subtotal' => 1499.00,
            'grand_total' => 1499.00,
            'total_items' => 1,
            'shipping_first_name' => 'John',
            'shipping_last_name' => 'Buyer',
            'shipping_phone' => '9876543210',
            'shipping_address_line_1' => '123 Temple St',
            'shipping_city' => 'Chennai',
            'shipping_state' => 'Tamil Nadu',
            'shipping_postal_code' => '600001',
            'billing_first_name' => 'John',
            'billing_last_name' => 'Buyer',
            'billing_phone' => '9876543210',
            'billing_address_line_1' => '123 Temple St',
            'billing_city' => 'Chennai',
            'billing_state' => 'Tamil Nadu',
            'billing_postal_code' => '600001',
        ]);

        OrderItem::create([
            'order_id' => $this->order->id,
            'product_id' => $this->product->id,
            'product_variant_id' => $this->variant->id,
            'product_name' => 'Kanchipuram Silk Saree',
            'sku' => $this->variant->sku,
            'quantity' => 2,
            'unit_mrp' => 2999.00,
            'unit_price' => 1499.00,
            'total_price' => 2998.00,
        ]);
    }

    public function test_can_create_cashfree_order_session(): void
    {
        $this->actingAs($this->customer);

        $this->mock(CashfreePaymentService::class, function ($mock) {
            $mock->shouldReceive('createCashfreeOrder')
                ->once()
                ->with('VIBE-TEST123', 1499.00, \Mockery::type('array'), \Mockery::type('array'))
                ->andReturn([
                    'cf_order_id' => '12345678',
                    'order_id' => 'VIBE-TEST123',
                    'order_status' => 'ACTIVE',
                    'payment_session_id' => 'session_test_abc123',
                    'order_amount' => 1499.00,
                    'order_currency' => 'INR',
                ]);
            $mock->shouldReceive('getEnvironment')
                ->andReturn('sandbox');
        });

        $response = $this->postJson('/api/payment/cashfree/create', [
            'order_id' => $this->order->id,
        ]);

        $response->assertStatus(200)
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.payment_session_id', 'session_test_abc123')
            ->assertJsonPath('data.cf_order_id', '12345678')
            ->assertJsonPath('data.order_number', 'VIBE-TEST123');

        $this->assertDatabaseHas('payments', [
            'order_id' => $this->order->id,
            'gateway' => 'cashfree',
            'gateway_order_id' => 'VIBE-TEST123',
            'status' => 'pending',
        ]);
    }

    public function test_can_verify_payment_success_and_commit_stock(): void
    {
        $this->actingAs($this->customer);

        // Reserve stock to simulate checkout stage
        $this->variant->stock_quantity = 10;
        $this->variant->reserved_quantity = 2;
        $this->variant->save();

        $this->mock(CashfreePaymentService::class, function ($mock) {
            $mock->shouldReceive('getOrderPayments')
                ->once()
                ->with('VIBE-TEST123')
                ->andReturn([
                    [
                        'cf_payment_id' => 987654321,
                        'payment_status' => 'SUCCESS',
                        'payment_amount' => 1499.00,
                        'payment_currency' => 'INR',
                        'payment_group' => 'upi',
                        'payment_time' => '2026-08-20T12:00:00Z',
                    ]
                ]);
        });

        $response = $this->postJson('/api/payment/cashfree/verify', [
            'order_id' => $this->order->id,
            'cashfree_order_id' => 'VIBE-TEST123',
        ]);

        $response->assertStatus(200)
            ->assertJsonPath('success', true)
            ->assertJsonPath('message', 'Payment verified and captured successfully.');

        $this->assertDatabaseHas('orders', [
            'id' => $this->order->id,
            'payment_status' => 'paid',
            'status' => 'processing',
            'payment_gateway' => 'cashfree',
            'gateway_payment_id' => '987654321',
        ]);

        $this->assertDatabaseHas('payments', [
            'order_id' => $this->order->id,
            'gateway' => 'cashfree',
            'gateway_payment_id' => '987654321',
            'status' => 'captured',
            'method' => 'upi',
        ]);

        $this->variant->refresh();
        $this->assertEquals(0, $this->variant->reserved_quantity);
    }

    public function test_verify_fails_when_no_successful_payment(): void
    {
        $this->actingAs($this->customer);

        $this->mock(CashfreePaymentService::class, function ($mock) {
            $mock->shouldReceive('getOrderPayments')
                ->once()
                ->with('VIBE-TEST123')
                ->andReturn([
                    [
                        'cf_payment_id' => 987654321,
                        'payment_status' => 'FAILED',
                        'payment_amount' => 1499.00,
                        'payment_message' => 'Bank server timed out',
                    ]
                ]);
        });

        $response = $this->postJson('/api/payment/cashfree/verify', [
            'order_id' => $this->order->id,
            'cashfree_order_id' => 'VIBE-TEST123',
        ]);

        $response->assertStatus(400)
            ->assertJsonPath('success', false)
            ->assertJsonPath('message', 'Bank server timed out');

        $this->assertDatabaseHas('orders', [
            'id' => $this->order->id,
            'payment_status' => 'pending',
        ]);
    }

    public function test_verify_fails_on_amount_mismatch(): void
    {
        $this->actingAs($this->customer);

        $this->mock(CashfreePaymentService::class, function ($mock) {
            $mock->shouldReceive('getOrderPayments')
                ->once()
                ->with('VIBE-TEST123')
                ->andReturn([
                    [
                        'cf_payment_id' => 987654321,
                        'payment_status' => 'SUCCESS',
                        'payment_amount' => 999.00, // Tampered amount
                    ]
                ]);
        });

        $response = $this->postJson('/api/payment/cashfree/verify', [
            'order_id' => $this->order->id,
            'cashfree_order_id' => 'VIBE-TEST123',
        ]);

        $response->assertStatus(400)
            ->assertJsonPath('success', false)
            ->assertJsonPath('message', 'Payment amount mismatch detected. Please contact support.');
    }

    public function test_can_cancel_payment_and_release_stock(): void
    {
        $this->actingAs($this->customer);

        // Setup stock reservation state
        $this->variant->stock_quantity = 8;
        $this->variant->reserved_quantity = 2;
        $this->variant->save();

        $response = $this->postJson('/api/payment/cashfree/cancel', [
            'order_id' => $this->order->id,
            'reason' => 'Customer dropped Cashfree checkout modal',
        ]);

        $response->assertStatus(200)
            ->assertJsonPath('success', true);

        $this->assertDatabaseHas('orders', [
            'id' => $this->order->id,
            'payment_status' => 'failed',
            'status' => 'cancelled',
            'cancellation_reason' => 'Customer dropped Cashfree checkout modal',
        ]);

        $this->variant->refresh();
        $this->assertEquals(10, $this->variant->stock_quantity);
        $this->assertEquals(0, $this->variant->reserved_quantity);
    }

    public function test_can_process_webhook_payment_success(): void
    {
        $this->mock(CashfreePaymentService::class, function ($mock) {
            $mock->shouldReceive('verifyWebhookSignature')
                ->once()
                ->andReturn(true);
        });

        // Setup stock reservation state
        $this->variant->stock_quantity = 8;
        $this->variant->reserved_quantity = 2;
        $this->variant->save();

        $payload = [
            'type' => 'PAYMENT_SUCCESS_WEBHOOK',
            'event_time' => '2026-08-20T12:00:00Z',
            'data' => [
                'order' => [
                    'order_id' => 'VIBE-TEST123',
                    'order_amount' => 1499.00,
                    'order_currency' => 'INR',
                ],
                'payment' => [
                    'cf_payment_id' => 'cf_pay_999888',
                    'payment_status' => 'SUCCESS',
                    'payment_amount' => 1499.00,
                    'payment_group' => 'upi',
                    'payment_message' => 'Transaction Successful',
                ]
            ]
        ];

        $response = $this->withHeaders([
            'x-webhook-signature' => 'valid-signature-base64',
            'x-webhook-timestamp' => '1755700000',
        ])->postJson('/api/payment/cashfree/webhook', $payload);

        $response->assertStatus(200);

        $this->assertDatabaseHas('orders', [
            'id' => $this->order->id,
            'payment_status' => 'paid',
            'status' => 'processing',
            'payment_gateway' => 'cashfree',
            'gateway_payment_id' => 'cf_pay_999888',
        ]);

        $this->variant->refresh();
        $this->assertEquals(0, $this->variant->reserved_quantity);
    }

    public function test_webhook_invalid_signature_rejected(): void
    {
        $this->mock(CashfreePaymentService::class, function ($mock) {
            $mock->shouldReceive('verifyWebhookSignature')
                ->once()
                ->andReturn(false);
        });

        $payload = [
            'type' => 'PAYMENT_SUCCESS_WEBHOOK',
            'data' => [
                'order' => ['order_id' => 'VIBE-TEST123'],
                'payment' => ['cf_payment_id' => '123'],
            ]
        ];

        $response = $this->withHeaders([
            'x-webhook-signature' => 'invalid-signature',
            'x-webhook-timestamp' => '1755700000',
        ])->postJson('/api/payment/cashfree/webhook', $payload);

        $response->assertStatus(400)
            ->assertJsonPath('message', 'Invalid webhook signature');
    }

    public function test_duplicate_webhook_success_is_idempotent(): void
    {
        // First mark order as paid
        $this->order->payment_status = 'paid';
        $this->order->status = 'processing';
        $this->order->save();

        $this->mock(CashfreePaymentService::class, function ($mock) {
            $mock->shouldReceive('verifyWebhookSignature')
                ->once()
                ->andReturn(true);
        });

        $payload = [
            'type' => 'PAYMENT_SUCCESS_WEBHOOK',
            'data' => [
                'order' => [
                    'order_id' => 'VIBE-TEST123',
                    'order_amount' => 1499.00,
                ],
                'payment' => [
                    'cf_payment_id' => 'cf_pay_999888',
                    'payment_status' => 'SUCCESS',
                    'payment_amount' => 1499.00,
                    'payment_group' => 'upi',
                ]
            ]
        ];

        $response = $this->withHeaders([
            'x-webhook-signature' => 'valid-signature',
            'x-webhook-timestamp' => '1755700000',
        ])->postJson('/api/payment/cashfree/webhook', $payload);

        $response->assertStatus(200)
            ->assertJsonPath('message', 'Payment already processed');
    }

    public function test_webhook_payment_failed_event(): void
    {
        $this->mock(CashfreePaymentService::class, function ($mock) {
            $mock->shouldReceive('verifyWebhookSignature')
                ->once()
                ->andReturn(true);
        });

        // Setup stock reservation state
        $this->variant->stock_quantity = 8;
        $this->variant->reserved_quantity = 2;
        $this->variant->save();

        // Create pending payment record
        Payment::create([
            'order_id' => $this->order->id,
            'gateway' => 'cashfree',
            'gateway_order_id' => 'VIBE-TEST123',
            'status' => 'pending',
            'amount' => 1499.00,
            'method' => 'online',
        ]);

        $payload = [
            'type' => 'PAYMENT_FAILED_WEBHOOK',
            'data' => [
                'order' => [
                    'order_id' => 'VIBE-TEST123',
                ],
                'payment' => [
                    'cf_payment_id' => 'cf_pay_999888',
                    'payment_status' => 'FAILED',
                    'payment_message' => 'Card authorization declined by issuer',
                ]
            ]
        ];

        $response = $this->withHeaders([
            'x-webhook-signature' => 'valid-signature',
            'x-webhook-timestamp' => '1755700000',
        ])->postJson('/api/payment/cashfree/webhook', $payload);

        $response->assertStatus(200);

        $this->assertDatabaseHas('orders', [
            'id' => $this->order->id,
            'payment_status' => 'failed',
            'status' => 'cancelled',
            'cancellation_reason' => 'Card authorization declined by issuer',
        ]);
    }

    public function test_webhook_refund_processed(): void
    {
        $this->mock(CashfreePaymentService::class, function ($mock) {
            $mock->shouldReceive('verifyWebhookSignature')
                ->once()
                ->andReturn(true);
        });

        // Create captured payment record
        $payment = Payment::create([
            'order_id' => $this->order->id,
            'gateway' => 'cashfree',
            'gateway_order_id' => 'VIBE-TEST123',
            'gateway_payment_id' => 'cf_pay_999888',
            'status' => 'captured',
            'amount' => 1499.00,
            'method' => 'online',
        ]);

        $payload = [
            'type' => 'REFUND_STATUS_WEBHOOK',
            'data' => [
                'order' => [
                    'order_id' => 'VIBE-TEST123',
                ],
                'payment' => [
                    'cf_payment_id' => 'cf_pay_999888',
                ],
                'refund' => [
                    'cf_refund_id' => 'cf_ref_777666',
                    'refund_amount' => 1499.00,
                    'refund_status' => 'SUCCESS',
                    'refund_note' => 'Approved customer return refund',
                ]
            ]
        ];

        $response = $this->withHeaders([
            'x-webhook-signature' => 'valid-signature',
            'x-webhook-timestamp' => '1755700000',
        ])->postJson('/api/payment/cashfree/webhook', $payload);

        $response->assertStatus(200);

        $this->assertDatabaseHas('orders', [
            'id' => $this->order->id,
            'payment_status' => 'refunded',
            'status' => 'refunded',
        ]);

        $this->assertDatabaseHas('refunds', [
            'payment_id' => $payment->id,
            'order_id' => $this->order->id,
            'gateway_refund_id' => 'cf_ref_777666',
            'amount' => 1499.00,
            'status' => 'processed',
        ]);
    }
}
