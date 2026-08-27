<?php

namespace Tests\Feature;

use App\Models\Order;
use App\Models\Payment;
use App\Models\User;
use App\Models\Role;
use App\Models\Permission;
use App\Services\CashfreeService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CashfreeReportApiTest extends TestCase
{
    use RefreshDatabase;

    protected $adminUser;
    protected $customerUser;
    protected $order1;
    protected $order2;
    protected $payment1;
    protected $payment2;
    protected $payment3;

    protected function setUp(): void
    {
        parent::setUp();

        // Get Super Admin Role & assign permission
        $superAdminRole = Role::firstOrCreate(['name' => 'super_admin']);
        $manageReportsPerm = Permission::firstOrCreate(['name' => 'manage_reports'], ['module' => 'reports', 'action' => 'view']);
        $superAdminRole->permissions()->syncWithoutDetaching([$manageReportsPerm->id]);

        $this->adminUser = User::factory()->create([
            'email' => 'admin@mayasree.com',
            'first_name' => 'Admin',
            'last_name' => 'Super',
        ]);
        $this->adminUser->assignRole($superAdminRole);

        // Create Regular Customer without admin roles
        $this->customerUser = User::factory()->create([
            'email' => 'customer@mayasree.com',
            'first_name' => 'Priya',
            'last_name' => 'Sharma',
            'role_id' => null,
        ]);

        // Create Sample Orders
        $this->order1 = Order::create([
            'uuid' => 'ord-uuid-001',
            'order_number' => 'MSF-2026-01001',
            'user_id' => $this->customerUser->id,
            'status' => 'processing',
            'payment_status' => 'paid',
            'payment_method' => 'upi',
            'payment_gateway' => 'cashfree',
            'gateway_order_id' => 'MSF-2026-01001',
            'gateway_payment_id' => 'cf_pay_111222',
            'subtotal' => 2499.00,
            'grand_total' => 2499.00,
            'total_items' => 1,
            'shipping_first_name' => 'Priya',
            'shipping_last_name' => 'Sharma',
            'shipping_phone' => '9876543210',
            'shipping_address_line_1' => '12 Gandhi Road',
            'shipping_city' => 'Kochi',
            'shipping_state' => 'Kerala',
            'shipping_postal_code' => '682001',
            'billing_first_name' => 'Priya',
            'billing_last_name' => 'Sharma',
            'billing_phone' => '9876543210',
            'billing_address_line_1' => '12 Gandhi Road',
            'billing_city' => 'Kochi',
            'billing_state' => 'Kerala',
            'billing_postal_code' => '682001',
        ]);

        $this->order2 = Order::create([
            'uuid' => 'ord-uuid-002',
            'order_number' => 'MSF-2026-01002',
            'user_id' => $this->customerUser->id,
            'status' => 'order_placed',
            'payment_status' => 'pending',
            'payment_method' => 'card',
            'payment_gateway' => 'cashfree',
            'gateway_order_id' => 'MSF-2026-01002',
            'subtotal' => 1899.00,
            'grand_total' => 1899.00,
            'total_items' => 1,
            'shipping_first_name' => 'Priya',
            'shipping_last_name' => 'Sharma',
            'shipping_phone' => '9876543210',
            'shipping_address_line_1' => '12 Gandhi Road',
            'shipping_city' => 'Kochi',
            'shipping_state' => 'Kerala',
            'shipping_postal_code' => '682001',
            'billing_first_name' => 'Priya',
            'billing_last_name' => 'Sharma',
            'billing_phone' => '9876543210',
            'billing_address_line_1' => '12 Gandhi Road',
            'billing_city' => 'Kochi',
            'billing_state' => 'Kerala',
            'billing_postal_code' => '682001',
        ]);

        // Create Sample Payments
        $this->payment1 = Payment::create([
            'order_id' => $this->order1->id,
            'gateway' => 'cashfree',
            'gateway_payment_id' => 'cf_pay_111222',
            'gateway_order_id' => 'MSF-2026-01001',
            'method' => 'upi',
            'amount' => 2499.00,
            'currency' => 'INR',
            'status' => 'captured',
            'paid_at' => now(),
            'gateway_response' => [
                'cf_payment_id' => 'cf_pay_111222',
                'payment_status' => 'SUCCESS',
                'payment_group' => 'upi',
            ],
            'created_at' => now(),
        ]);

        $this->payment2 = Payment::create([
            'order_id' => $this->order2->id,
            'gateway' => 'cashfree',
            'gateway_order_id' => 'MSF-2026-01002',
            'method' => 'card',
            'amount' => 1899.00,
            'currency' => 'INR',
            'status' => 'pending',
            'created_at' => now(),
        ]);

        $this->payment3 = Payment::create([
            'order_id' => $this->order2->id,
            'gateway' => 'cashfree',
            'gateway_order_id' => 'MSF-2026-01002',
            'method' => 'card',
            'amount' => 1899.00,
            'currency' => 'INR',
            'status' => 'failed',
            'failure_reason' => 'OTP verification expired',
            'created_at' => now(),
        ]);
    }

    public function test_admin_can_retrieve_payments_report(): void
    {
        $this->actingAs($this->adminUser, 'sanctum');

        $response = $this->getJson('/api/admin/reports/payments');

        $response->assertStatus(200)
            ->assertJsonPath('success', true)
            ->assertJsonPath('message', 'Cashfree payments report loaded successfully')
            ->assertJsonCount(3, 'data');

        $firstItem = $response->json('data.0');
        $this->assertArrayHasKey('order_id', $firstItem);
        $this->assertArrayHasKey('cashfree_order_id', $firstItem);
        $this->assertArrayHasKey('payment_id', $firstItem);
        $this->assertArrayHasKey('customer_name', $firstItem);
        $this->assertArrayHasKey('customer_email', $firstItem);
        $this->assertArrayHasKey('order_amount', $firstItem);
        $this->assertArrayHasKey('payment_amount', $firstItem);
        $this->assertArrayHasKey('payment_method', $firstItem);
        $this->assertArrayHasKey('payment_status', $firstItem);
        $this->assertArrayHasKey('cashfree_status', $firstItem);
        $this->assertArrayHasKey('payment_date', $firstItem);
    }

    public function test_admin_can_filter_payments_by_status_and_method(): void
    {
        $this->actingAs($this->adminUser, 'sanctum');

        // Filter by status=captured
        $capturedRes = $this->getJson('/api/admin/reports/payments?payment_status=captured');
        $capturedRes->assertStatus(200)
            ->assertJsonPath('success', true)
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.payment_status', 'captured');

        // Filter by method=upi
        $upiRes = $this->getJson('/api/admin/reports/payments?payment_method=upi');
        $upiRes->assertStatus(200)
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.payment_method', 'UPI');

        // Search by Order Number
        $searchRes = $this->getJson('/api/admin/reports/payments?search=MSF-2026-01001');
        $searchRes->assertStatus(200)
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.order_id', 'MSF-2026-01001');
    }

    public function test_admin_can_retrieve_payments_summary_kpis(): void
    {
        $this->actingAs($this->adminUser, 'sanctum');

        $response = $this->getJson('/api/admin/reports/payments/summary');

        $response->assertStatus(200)
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.today_total_payments', 3)
            ->assertJsonPath('data.successful_payments', 1)
            ->assertJsonPath('data.pending_payments', 1)
            ->assertJsonPath('data.failed_payments', 1)
            ->assertJsonPath('data.today_total_collection', 2499);
    }

    public function test_admin_can_retrieve_settlements_report_with_mock(): void
    {
        $this->actingAs($this->adminUser, 'sanctum');

        $this->mock(CashfreeService::class, function ($mock) {
            $mock->shouldReceive('isConfigured')->andReturn(true);
            $mock->shouldReceive('getSettlements')
                ->once()
                ->andReturn([
                    'settlements' => [
                        [
                            'cf_settlement_id' => 'SET10099',
                            'settlement_date' => '2026-08-26T14:30:00Z',
                            'settlement_amount' => 18450.00,
                            'settlement_status' => 'SETTLED',
                            'settlement_utr' => 'HDFC000192837465',
                            'settlement_type' => 'STANDARD',
                            'settlement_reference' => 'BATCH_889900',
                        ],
                        [
                            'cf_settlement_id' => 'SET10098',
                            'settlement_date' => '2026-08-25T11:00:00Z',
                            'settlement_amount' => 9200.00,
                            'settlement_status' => 'SETTLED',
                            'settlement_utr' => 'ICIC000998877665',
                            'settlement_type' => 'STANDARD',
                            'settlement_reference' => 'BATCH_889899',
                        ],
                    ],
                    'pagination' => [
                        'cursor' => 'next_cursor_token_xyz',
                        'limit' => 15,
                    ]
                ]);
        });

        $response = $this->getJson('/api/admin/reports/settlements');

        $response->assertStatus(200)
            ->assertJsonPath('success', true)
            ->assertJsonCount(2, 'data')
            ->assertJsonPath('data.0.settlement_id', 'SET10099')
            ->assertJsonPath('data.0.settlement_amount', 18450)
            ->assertJsonPath('data.0.settlement_status', 'SETTLED')
            ->assertJsonPath('data.0.utr', 'HDFC000192837465')
            ->assertJsonPath('data.0.settlement_type', 'STANDARD')
            ->assertJsonPath('data.0.settlement_reference', 'BATCH_889900');
    }

    public function test_admin_can_retrieve_settlements_summary_kpis_with_mock(): void
    {
        $this->actingAs($this->adminUser, 'sanctum');

        $this->mock(CashfreeService::class, function ($mock) {
            $mock->shouldReceive('isConfigured')->andReturn(true);
            $mock->shouldReceive('getSettlements')
                ->once()
                ->andReturn([
                    'settlements' => [
                        [
                            'cf_settlement_id' => 'SET10099',
                            'settlement_date' => '2026-08-26T14:30:00Z',
                            'settlement_amount' => 18450.00,
                            'settlement_status' => 'SETTLED',
                        ],
                        [
                            'cf_settlement_id' => 'SET10098',
                            'settlement_date' => '2026-08-25T11:00:00Z',
                            'settlement_amount' => 9200.00,
                            'settlement_status' => 'SETTLED',
                        ],
                    ]
                ]);
        });

        $response = $this->getJson('/api/admin/reports/settlements/summary');

        $response->assertStatus(200)
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.total_settled_amount', 27650)
            ->assertJsonPath('data.settlements_count', 2)
            ->assertJsonPath('data.latest_settlement', 18450)
            ->assertJsonPath('data.latest_settlement_date', '2026-08-26');
    }

    public function test_admin_can_verify_payment_live_with_cashfree(): void
    {
        $this->actingAs($this->adminUser, 'sanctum');

        $this->mock(CashfreeService::class, function ($mock) {
            $mock->shouldReceive('getOrderPayments')
                ->once()
                ->with('MSF-2026-01002')
                ->andReturn([
                    [
                        'cf_payment_id' => 'cf_pay_998877',
                        'payment_status' => 'SUCCESS',
                        'payment_amount' => 1899.00,
                        'payment_group' => 'card',
                    ]
                ]);
        });

        $response = $this->postJson("/api/admin/reports/payments/{$this->payment2->id}/verify-cashfree");

        $response->assertStatus(200)
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.cashfree_status', 'SUCCESS')
            ->assertJsonPath('data.status', 'captured')
            ->assertJsonPath('data.gateway_payment_id', 'cf_pay_998877');

        $this->payment2->refresh();
        $this->assertEquals('captured', $this->payment2->status);
        $this->assertEquals('cf_pay_998877', $this->payment2->gateway_payment_id);
    }

    public function test_unauthorized_user_cannot_access_reports(): void
    {
        // Unauthenticated guest
        auth('sanctum')->setUser(new User()); // clear Sanctum auth
        \Illuminate\Support\Facades\Auth::forgetGuards();

        $guestRes = $this->withHeaders(['Authorization' => ''])->getJson('/api/admin/reports/payments');
        $guestRes->assertStatus(401);

        // Authenticated customer without staff/admin role
        $this->actingAs($this->customerUser, 'sanctum');
        $customerRes = $this->getJson('/api/admin/reports/payments');
        $customerRes->assertStatus(403);
    }

    public function test_no_cashfree_secrets_exposed_in_api_response(): void
    {
        $this->actingAs($this->adminUser, 'sanctum');

        $response = $this->getJson('/api/admin/reports/payments');
        $content = $response->getContent();

        $this->assertStringNotContainsString('CASHFREE_SECRET_KEY', $content);
        $this->assertStringNotContainsString('x-client-secret', $content);
        $this->assertStringNotContainsString('secret_key', $content);
    }

    public function test_graceful_handling_when_cashfree_settlements_fails(): void
    {
        $this->actingAs($this->adminUser, 'sanctum');

        $this->mock(CashfreeService::class, function ($mock) {
            $mock->shouldReceive('isConfigured')->andReturn(true);
            $mock->shouldReceive('getSettlements')
                ->andThrow(new \Exception('Cashfree gateway service unavailable'));
        });

        $response = $this->getJson('/api/admin/reports/settlements');

        // Should return 200 with empty dataset or 500 without exposing exception traces
        $this->assertTrue($response->status() === 200 || $response->status() === 500);
        $responseContent = $response->getContent();
        $this->assertStringNotContainsString('Stack trace', $responseContent);
    }
}
