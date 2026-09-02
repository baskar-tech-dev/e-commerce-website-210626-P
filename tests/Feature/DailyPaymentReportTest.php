<?php

namespace Tests\Feature;

use App\Mail\DailyPaymentReportMail;
use App\Models\Order;
use App\Models\Payment;
use App\Models\Permission;
use App\Models\Role;
use App\Models\Setting;
use App\Models\User;
use App\Services\CashfreeService;
use App\Services\DailyPaymentReportService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class DailyPaymentReportTest extends TestCase
{
    use RefreshDatabase;

    protected $adminUser;
    protected $customerUser;
    protected $order1;
    protected $order2;
    protected $payment1;
    protected $payment2;

    protected function setUp(): void
    {
        parent::setUp();

        Carbon::setTestNow(Carbon::parse('2026-09-01 08:30:00', 'Asia/Kolkata'));

        $superAdminRole = Role::firstOrCreate(['name' => 'super_admin']);
        $paymentsPerm = Permission::firstOrCreate(['name' => 'payments'], ['module' => 'reports', 'action' => 'view']);
        $superAdminRole->permissions()->syncWithoutDetaching([$paymentsPerm->id]);

        $this->adminUser = User::factory()->create([
            'email' => 'admin@mayasree.com',
            'first_name' => 'Admin',
            'last_name' => 'Super',
        ]);
        $this->adminUser->assignRole($superAdminRole);

        $this->customerUser = User::factory()->create([
            'email' => 'customer@mayasree.com',
            'first_name' => 'Ananya',
            'last_name' => 'Sharma',
        ]);

        // Set test now to yesterday when orders were placed
        Carbon::setTestNow(Carbon::parse('2026-08-31 14:30:00', 'Asia/Kolkata'));

        // Order 1: Paid online via Cashfree UPI
        $this->order1 = Order::create([
            'uuid' => 'ord-uuid-rpt-001',
            'order_number' => 'MSF-2026-09001',
            'user_id' => $this->customerUser->id,
            'status' => 'processing',
            'payment_status' => 'paid',
            'payment_method' => 'upi',
            'payment_gateway' => 'cashfree',
            'gateway_order_id' => 'MSF-2026-09001',
            'gateway_payment_id' => 'cf_pay_9001',
            'subtotal' => 2999.00,
            'grand_total' => 2999.00,
            'total_items' => 1,
            'shipping_first_name' => 'Ananya',
            'shipping_last_name' => 'Sharma',
            'shipping_phone' => '9876543210',
            'shipping_address_line_1' => '12 MG Road',
            'shipping_city' => 'Bengaluru',
            'shipping_state' => 'Karnataka',
            'shipping_postal_code' => '560001',
            'billing_first_name' => 'Ananya',
            'billing_last_name' => 'Sharma',
            'billing_phone' => '9876543210',
            'billing_address_line_1' => '12 MG Road',
            'billing_city' => 'Bengaluru',
            'billing_state' => 'Karnataka',
            'billing_postal_code' => '560001',
        ]);

        $this->payment1 = Payment::create([
            'order_id' => $this->order1->id,
            'gateway' => 'cashfree',
            'gateway_payment_id' => 'cf_pay_9001',
            'gateway_order_id' => 'MSF-2026-09001',
            'method' => 'upi',
            'amount' => 2999.00,
            'currency' => 'INR',
            'status' => 'captured',
            'paid_at' => now(),
        ]);

        // Order 2: COD Order
        $this->order2 = Order::create([
            'uuid' => 'ord-uuid-rpt-002',
            'order_number' => 'MSF-2026-09002',
            'user_id' => $this->customerUser->id,
            'status' => 'order_placed',
            'payment_status' => 'pending',
            'payment_method' => 'cod',
            'subtotal' => 1499.00,
            'grand_total' => 1499.00,
            'total_items' => 1,
            'shipping_first_name' => 'Ananya',
            'shipping_last_name' => 'Sharma',
            'shipping_phone' => '9876543210',
            'shipping_address_line_1' => '12 MG Road',
            'shipping_city' => 'Bengaluru',
            'shipping_state' => 'Karnataka',
            'shipping_postal_code' => '560001',
            'billing_first_name' => 'Ananya',
            'billing_last_name' => 'Sharma',
            'billing_phone' => '9876543210',
            'billing_address_line_1' => '12 MG Road',
            'billing_city' => 'Bengaluru',
            'billing_state' => 'Karnataka',
            'billing_postal_code' => '560001',
        ]);

        // Advance test clock to 8:30 AM morning after
        Carbon::setTestNow(Carbon::parse('2026-09-01 08:30:00', 'Asia/Kolkata'));
    }

    public function test_service_generates_accurate_report_data(): void

    {
        $this->mock(CashfreeService::class, function ($mock) {
            $mock->shouldReceive('isConfigured')->andReturn(false);
            $mock->shouldReceive('getEnvironment')->andReturn('sandbox');
        });

        $service = app(DailyPaymentReportService::class);
        $reportData = $service->generateReportData(Carbon::yesterday('Asia/Kolkata'));

        $this->assertArrayHasKey('kpis', $reportData);
        $this->assertArrayHasKey('method_breakdown', $reportData);
        $this->assertArrayHasKey('transactions', $reportData);

        $kpis = $reportData['kpis'];
        $this->assertEquals(4498.00, $kpis['gross_total_revenue']);
        $this->assertEquals(2999.00, $kpis['total_online_collection']);
        $this->assertEquals(1499.00, $kpis['cod_total_amount']);
        $this->assertEquals(1, $kpis['successful_count']);
        $this->assertEquals(1, $kpis['cod_count']);
        $this->assertEquals(2, $reportData['transactions_count']);
    }

    public function test_csv_content_generation_includes_headers_and_transactions(): void
    {
        $this->mock(CashfreeService::class, function ($mock) {
            $mock->shouldReceive('isConfigured')->andReturn(false);
            $mock->shouldReceive('getEnvironment')->andReturn('sandbox');
        });

        $service = app(DailyPaymentReportService::class);
        $reportData = $service->generateReportData(Carbon::yesterday('Asia/Kolkata'));
        $csv = $service->generateCsvContent($reportData);

        $this->assertStringContainsString('MAYA SREE FASHION - DAILY PAYMENT & SETTLEMENT REPORT', $csv);
        $this->assertStringContainsString('MSF-2026-09001', $csv);
        $this->assertStringContainsString('MSF-2026-09002', $csv);
        $this->assertStringContainsString('Ananya Sharma', $csv);
        $this->assertStringContainsString('2999.00', $csv);
    }


    public function test_service_dispatches_daily_payment_report_email(): void
    {
        Mail::fake();

        Setting::set('daily_payment_report_recipients', 'finance@mayasree.com, client@mayasree.com', 'email');
        Setting::set('daily_payment_report_enabled', true, 'email', 'boolean');

        $service = app(DailyPaymentReportService::class);
        $result = $service->sendDailyReport();

        $this->assertTrue($result['success']);

        Mail::assertSent(DailyPaymentReportMail::class, function ($mail) {
            $hasRecipient = $mail->hasTo('finance@mayasree.com');
            $hasCc = $mail->hasCc('client@mayasree.com');
            $hasAttachment = count($mail->attachments()) > 0;
            return $hasRecipient && $hasCc && $hasAttachment;
        });
    }

    public function test_artisan_command_dry_run_executes_successfully(): void
    {
        $this->artisan('reports:send-daily-payment-report', ['--dry-run' => true])
            ->expectsOutputToContain('MAYA SREE FASHION • DAILY CASHFREE PAYMENT & SETTLEMENT REPORT')
            ->expectsOutputToContain('Gross Total Revenue')
            ->assertExitCode(0);
    }

    public function test_admin_can_preview_daily_payment_report_via_api(): void
    {
        $this->actingAs($this->adminUser, 'sanctum');

        $this->mock(CashfreeService::class, function ($mock) {
            $mock->shouldReceive('isConfigured')->andReturn(false);
            $mock->shouldReceive('getEnvironment')->andReturn('sandbox');
        });

        $response = $this->getJson('/api/admin/reports/daily-payment-report/preview?date=yesterday');

        $response->assertStatus(200)
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.kpis.gross_total_revenue', 4498)
            ->assertJsonPath('data.kpis.total_online_collection', 2999)
            ->assertJsonPath('data.kpis.cod_total_amount', 1499);
    }


    public function test_admin_can_send_daily_payment_report_on_demand_via_api(): void
    {
        Mail::fake();
        $this->actingAs($this->adminUser, 'sanctum');

        $response = $this->postJson('/api/admin/reports/daily-payment-report/send', [
            'date' => 'yesterday',
            'recipient_email' => 'custom-stakeholder@mayasree.com',
        ]);

        $response->assertStatus(200)
            ->assertJsonPath('success', true);

        Mail::assertSent(DailyPaymentReportMail::class, function ($mail) {
            return $mail->hasTo('custom-stakeholder@mayasree.com');
        });
    }

    public function test_report_execution_maintains_database_log_and_file_log(): void
    {
        Mail::fake();

        $service = app(DailyPaymentReportService::class);
        $result = $service->sendDailyReport('yesterday', 'finance@mayasree.com', true, 'scheduled');

        $this->assertTrue($result['success']);

        // Verify database log entry exists
        $this->assertDatabaseHas('payment_report_logs', [
            'status' => 'success',
            'channel' => 'scheduled',
            'gross_revenue' => 4498.00,
            'online_collection' => 2999.00,
            'cod_amount' => 1499.00,
            'transactions_count' => 2,
        ]);

        $latestLog = \App\Models\PaymentReportLog::latest()->first();
        $this->assertNotNull($latestLog);
        $this->assertContains('finance@mayasree.com', $latestLog->recipients);

        // Verify physical file log exists
        $logFilePath = storage_path('logs/daily_payment_report.log');
        $this->assertFileExists($logFilePath);
        $logContent = file_get_contents($logFilePath);
        $this->assertStringContainsString('SUCCESS', $logContent);
        $this->assertStringContainsString('finance@mayasree.com', $logContent);
    }

    public function test_admin_can_retrieve_daily_payment_report_logs_via_api(): void
    {
        $this->actingAs($this->adminUser, 'sanctum');

        \App\Models\PaymentReportLog::create([
            'report_date' => '2026-08-31',
            'triggered_at' => now(),
            'status' => 'success',
            'channel' => 'scheduled',
            'recipients' => ['finance@mayasree.com'],
            'gross_revenue' => 12500.00,
            'online_collection' => 10000.00,
            'cod_amount' => 2500.00,
            'net_settled' => 9780.00,
            'orders_count' => 5,
            'transactions_count' => 5,
            'settlement_utr' => 'CFUTR998811',
            'duration_ms' => 120,
        ]);

        $response = $this->getJson('/api/admin/reports/daily-payment-report/logs');

        $response->assertStatus(200)
            ->assertJsonPath('success', true)
            ->assertJsonPath('message', 'Daily payment report logs retrieved successfully')
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.status', 'success')
            ->assertJsonPath('data.0.channel', 'scheduled')
            ->assertJsonPath('data.0.gross_revenue', '12500.00')
            ->assertJsonPath('data.0.settlement_utr', 'CFUTR998811');
    }
}

