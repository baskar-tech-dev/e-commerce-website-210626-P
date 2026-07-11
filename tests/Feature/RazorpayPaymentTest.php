<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\Category;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Payment;
use App\Services\RazorpayService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RazorpayPaymentTest extends TestCase
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
        ]);

        $this->category = Category::create([
            'name' => 'Activewear',
            'slug' => 'activewear',
            'is_active' => true,
        ]);

        $this->product = Product::create([
            'category_id' => $this->category->id,
            'name' => 'Pro Joggers',
            'slug' => 'pro-joggers',
            'mrp' => 1999.00,
            'selling_price' => 1499.00,
            'is_active' => true,
        ]);

        $this->variant = ProductVariant::create([
            'product_id' => $this->product->id,
            'sku' => 'PRO-JOG-M',
            'size' => 'M',
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
            'payment_method' => 'razorpay',
            'subtotal' => 1499.00,
            'grand_total' => 1499.00,
            'total_items' => 1,
            'shipping_first_name' => 'John',
            'shipping_last_name' => 'Buyer',
            'shipping_phone' => '9876543210',
            'shipping_address_line_1' => '123 Test St',
            'shipping_city' => 'Bangalore',
            'shipping_state' => 'Karnataka',
            'shipping_postal_code' => '560001',
            'billing_first_name' => 'John',
            'billing_last_name' => 'Buyer',
            'billing_phone' => '9876543210',
            'billing_address_line_1' => '123 Test St',
            'billing_city' => 'Bangalore',
            'billing_state' => 'Karnataka',
            'billing_postal_code' => '560001',
        ]);

        OrderItem::create([
            'order_id' => $this->order->id,
            'product_id' => $this->product->id,
            'product_variant_id' => $this->variant->id,
            'product_name' => 'Pro Joggers',
            'sku' => $this->variant->sku,
            'quantity' => 2,
            'unit_mrp' => 1999.00,
            'unit_price' => 1499.00,
            'total_price' => 2998.00,
        ]);
    }

    public function test_can_create_razorpay_order(): void
    {
        $this->actingAs($this->customer);

        $this->mock(RazorpayService::class, function ($mock) {
            $mock->shouldReceive('createRazorpayOrder')
                ->once()
                ->with('VIBE-TEST123', 1499.00)
                ->andReturn([
                    'id' => 'order_fake999',
                    'amount' => 149900,
                    'currency' => 'INR',
                ]);
        });

        $response = $this->postJson('/api/payment/create-order', [
            'order_id' => $this->order->id,
        ]);

        $response->assertStatus(200)
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.razorpay_order_id', 'order_fake999');

        $this->assertDatabaseHas('payments', [
            'order_id' => $this->order->id,
            'gateway' => 'razorpay',
            'gateway_order_id' => 'order_fake999',
            'status' => 'pending',
        ]);
    }

    public function test_can_verify_payment_signature(): void
    {
        $this->actingAs($this->customer);

        // First reserve stock to simulate order creation flow
        $this->variant->stock_quantity = 10;
        $this->variant->reserved_quantity = 2;
        $this->variant->save();

        $this->mock(RazorpayService::class, function ($mock) {
            $mock->shouldReceive('verifySignature')
                ->once()
                ->with('order_fake999', 'pay_fake888', 'sig_fake777')
                ->andReturn(true);
        });

        $response = $this->postJson('/api/payment/verify', [
            'order_id' => $this->order->id,
            'razorpay_order_id' => 'order_fake999',
            'razorpay_payment_id' => 'pay_fake888',
            'razorpay_signature' => 'sig_fake777',
        ]);

        $response->assertStatus(200)
            ->assertJsonPath('success', true);

        $this->assertDatabaseHas('orders', [
            'id' => $this->order->id,
            'payment_status' => 'paid',
            'status' => 'processing',
        ]);

        $this->assertDatabaseHas('payments', [
            'order_id' => $this->order->id,
            'gateway_order_id' => 'order_fake999',
            'gateway_payment_id' => 'pay_fake888',
            'status' => 'captured',
        ]);

        // Stock quantity should be reduced from 10 to 8 during checkout, and reserved should go from 2 to 0 on commit
        $this->variant->refresh();
        $this->assertEquals(0, $this->variant->reserved_quantity);
    }

    public function test_can_cancel_payment_and_release_stock(): void
    {
        $this->actingAs($this->customer);

        // Setup stock reservation state
        $this->variant->stock_quantity = 8;
        $this->variant->reserved_quantity = 2;
        $this->variant->save();

        $response = $this->postJson('/api/payment/cancel', [
            'order_id' => $this->order->id,
            'reason' => 'User aborted payment',
        ]);

        $response->assertStatus(200)
            ->assertJsonPath('success', true);

        $this->assertDatabaseHas('orders', [
            'id' => $this->order->id,
            'payment_status' => 'failed',
            'status' => 'cancelled',
        ]);

        // Stock quantity should return back to 10 and reserved should go down to 0
        $this->variant->refresh();
        $this->assertEquals(10, $this->variant->stock_quantity);
        $this->assertEquals(0, $this->variant->reserved_quantity);
    }

    public function test_can_process_webhook_payment_success(): void
    {
        $this->mock(RazorpayService::class, function ($mock) {
            $mock->shouldReceive('verifyWebhookSignature')
                ->once()
                ->andReturn(true);
        });

        // Setup stock reservation state
        $this->variant->stock_quantity = 8;
        $this->variant->reserved_quantity = 2;
        $this->variant->save();

        $payload = [
            'event' => 'order.paid',
            'payload' => [
                'order' => [
                    'entity' => [
                        'id' => 'order_fake999',
                        'receipt' => 'VIBE-TEST123',
                    ]
                ],
                'payment' => [
                    'entity' => [
                        'id' => 'pay_fake888',
                        'method' => 'upi',
                    ]
                ]
            ]
        ];

        $response = $this->withHeaders([
            'X-Razorpay-Signature' => 'fake-sig',
        ])->postJson('/api/payment/webhook', $payload);

        $response->assertStatus(200);

        $this->assertDatabaseHas('orders', [
            'id' => $this->order->id,
            'payment_status' => 'paid',
            'status' => 'processing',
        ]);

        $this->variant->refresh();
        $this->assertEquals(0, $this->variant->reserved_quantity);
    }

    public function test_verify_signature_mismatch_fails(): void
    {
        $this->actingAs($this->customer);

        $this->mock(RazorpayService::class, function ($mock) {
            $mock->shouldReceive('verifySignature')
                ->once()
                ->with('order_fake999', 'pay_fake888', 'sig_wrong')
                ->andReturn(false);
        });

        $response = $this->postJson('/api/payment/verify', [
            'order_id' => $this->order->id,
            'razorpay_order_id' => 'order_fake999',
            'razorpay_payment_id' => 'pay_fake888',
            'razorpay_signature' => 'sig_wrong',
        ]);

        $response->assertStatus(400)
            ->assertJsonPath('success', false)
            ->assertJsonPath('message', 'Payment signature verification failed.');

        $this->assertDatabaseHas('orders', [
            'id' => $this->order->id,
            'payment_status' => 'pending',
        ]);
    }

    public function test_duplicate_webhook_success_is_ignored(): void
    {
        // First mark order as paid
        $this->order->payment_status = 'paid';
        $this->order->status = 'processing';
        $this->order->save();

        $this->mock(RazorpayService::class, function ($mock) {
            $mock->shouldReceive('verifyWebhookSignature')
                ->once()
                ->andReturn(true);
        });

        $payload = [
            'event' => 'order.paid',
            'payload' => [
                'order' => [
                    'entity' => [
                        'id' => 'order_fake999',
                        'receipt' => 'VIBE-TEST123',
                    ]
                ],
                'payment' => [
                    'entity' => [
                        'id' => 'pay_fake888',
                        'method' => 'upi',
                    ]
                ]
            ]
        ];

        $response = $this->withHeaders([
            'X-Razorpay-Signature' => 'fake-sig',
        ])->postJson('/api/payment/webhook', $payload);

        $response->assertStatus(200)
            ->assertJsonPath('message', 'Payment already processed');
    }

    public function test_invalid_order_verification_fails(): void
    {
        $this->actingAs($this->customer);

        // Try to verify payment for non-existent order ID
        $response = $this->postJson('/api/payment/verify', [
            'order_id' => 99999,
            'razorpay_order_id' => 'order_fake999',
            'razorpay_payment_id' => 'pay_fake888',
            'razorpay_signature' => 'sig_fake777',
        ]);

        $response->assertStatus(422); // Validation error
    }

    public function test_webhook_payment_failed_event(): void
    {
        $this->mock(RazorpayService::class, function ($mock) {
            $mock->shouldReceive('verifyWebhookSignature')
                ->once()
                ->andReturn(true);
        });

        // Setup stock reservation state
        $this->variant->stock_quantity = 8;
        $this->variant->reserved_quantity = 2;
        $this->variant->save();

        // Set up a pending payment record
        Payment::create([
            'order_id' => $this->order->id,
            'gateway' => 'razorpay',
            'gateway_order_id' => 'order_fake999',
            'status' => 'pending',
            'amount' => 1499.00,
            'method' => 'online',
        ]);

        $payload = [
            'event' => 'payment.failed',
            'payload' => [
                'payment' => [
                    'entity' => [
                        'id' => 'pay_fake888',
                        'order_id' => 'order_fake999',
                        'error_description' => 'Bad credentials or timeout',
                    ]
                ]
            ]
        ];

        $response = $this->withHeaders([
            'X-Razorpay-Signature' => 'fake-sig',
        ])->postJson('/api/payment/webhook', $payload);

        $response->assertStatus(200);

        $this->assertDatabaseHas('orders', [
            'id' => $this->order->id,
            'payment_status' => 'failed',
            'status' => 'cancelled',
            'cancellation_reason' => 'Bad credentials or timeout',
        ]);
    }

    public function test_webhook_refund_processed(): void
    {
        $this->mock(RazorpayService::class, function ($mock) {
            $mock->shouldReceive('verifyWebhookSignature')
                ->once()
                ->andReturn(true);
        });

        // Set up a captured payment record
        $payment = Payment::create([
            'order_id' => $this->order->id,
            'gateway' => 'razorpay',
            'gateway_order_id' => 'order_fake999',
            'gateway_payment_id' => 'pay_fake888',
            'status' => 'captured',
            'amount' => 1499.00,
            'method' => 'online',
        ]);

        $payload = [
            'event' => 'refund.processed',
            'payload' => [
                'payment' => [
                    'entity' => [
                        'id' => 'pay_fake888',
                    ]
                ],
                'refund' => [
                    'entity' => [
                        'id' => 'rfnd_fake777',
                        'amount' => 149900,
                        'reason' => 'customer_request',
                    ]
                ]
            ]
        ];

        $response = $this->withHeaders([
            'X-Razorpay-Signature' => 'fake-sig',
        ])->postJson('/api/payment/webhook', $payload);

        $response->assertStatus(200);

        $this->assertDatabaseHas('orders', [
            'id' => $this->order->id,
            'payment_status' => 'refunded',
            'status' => 'refunded',
        ]);

        $this->assertDatabaseHas('refunds', [
            'payment_id' => $payment->id,
            'order_id' => $this->order->id,
            'gateway_refund_id' => 'rfnd_fake777',
            'amount' => 1499.00,
            'status' => 'processed',
        ]);
    }
}
