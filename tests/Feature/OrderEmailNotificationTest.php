<?php

namespace Tests\Feature;

use App\Mail\OrderPlacedNotificationMail;
use App\Mail\TestNotificationMail;
use App\Models\Category;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\Role;
use App\Models\Setting;
use App\Models\User;
use App\Services\OrderNotificationService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class OrderEmailNotificationTest extends TestCase
{
    use RefreshDatabase;

    protected User $adminUser;
    protected User $customerUser;
    protected ProductVariant $variant;

    protected function setUp(): void
    {
        parent::setUp();

        $adminRole = Role::firstOrCreate(['name' => 'admin'], [
            'display_name' => 'Administrator',
            'description' => 'Admin Role',
            'is_system' => true,
        ]);
        
        // Give manage_settings permission
        $perm = \App\Models\Permission::firstOrCreate(['name' => 'manage_settings'], [
            'display_name' => 'Manage Settings',
            'group' => 'settings',
        ]);
        $adminRole->permissions()->syncWithoutDetaching([$perm->id]);

        $this->adminUser = User::factory()->create([
            'role_id' => $adminRole->id,
            'email' => 'admin@mayasree.com',
        ]);

        $customerRole = Role::firstOrCreate(['name' => 'customer'], [
            'display_name' => 'Customer',
            'description' => 'Customer Role',
            'is_system' => true,
        ]);

        $this->customerUser = User::factory()->create([
            'role_id' => $customerRole->id,
            'email' => 'priya@example.com',
            'phone' => '9876543210',
            'first_name' => 'Priya',
            'last_name' => 'Sundar',
        ]);

        $category = Category::factory()->create(['name' => 'Silk Sarees']);
        $product = Product::factory()->create([
            'category_id' => $category->id,
            'name' => 'Kanjivaram Silk Saree - Ruby Red',
            'selling_price' => 2499.00,
            'mrp' => 4999.00,
        ]);

        $this->variant = ProductVariant::factory()->create([
            'product_id' => $product->id,
            'sku' => 'KANJI-RUBY-01',
            'size' => 'Free Size',
            'color' => 'Ruby Red',
            'stock_quantity' => 25,
            'selling_price' => 2499.00,
            'mrp' => 4999.00,
        ]);
    }

    public function test_order_placement_dispatches_notification_email_to_primary_email_when_enabled(): void
    {
        Mail::fake();

        // Configure email settings
        Setting::set('order_notification_enabled', true, 'email', 'boolean');
        Setting::set('primary_order_email', 'orders@mayasree.com', 'email', 'text');
        Setting::set('additional_order_emails', '', 'email', 'text');

        $response = $this->actingAs($this->customerUser)->postJson('/api/storefront/checkout', [
            'shipping_first_name' => 'Priya',
            'shipping_last_name' => 'Sundar',
            'shipping_phone' => '9876543210',
            'shipping_address_line_1' => 'No 45, Gandhi Road',
            'shipping_city' => 'Chennai',
            'shipping_state' => 'Tamil Nadu',
            'shipping_postal_code' => '600001',
            'payment_method' => 'cod',
            'items' => [
                [
                    'product_variant_id' => $this->variant->id,
                    'quantity' => 2,
                ],
            ],
        ]);

        $response->assertStatus(200)
            ->assertJsonPath('success', true);

        Mail::assertSent(OrderPlacedNotificationMail::class, function ($mail) {
            return $mail->hasTo('orders@mayasree.com') &&
                   $mail->order->items->count() === 1 &&
                   $mail->order->items->first()->sku === 'KANJI-RUBY-01';
        });
    }

    public function test_order_placement_includes_cc_recipients_if_configured(): void
    {
        Mail::fake();

        Setting::set('order_notification_enabled', true, 'email', 'boolean');
        Setting::set('primary_order_email', 'primary@mayasree.com', 'email', 'text');
        Setting::set('additional_order_emails', 'warehouse@mayasree.com, manager@mayasree.com', 'email', 'text');

        $response = $this->actingAs($this->customerUser)->postJson('/api/storefront/checkout', [
            'shipping_first_name' => 'Priya',
            'shipping_last_name' => 'Sundar',
            'shipping_phone' => '9876543210',
            'shipping_address_line_1' => 'No 45, Gandhi Road',
            'shipping_city' => 'Chennai',
            'shipping_state' => 'Tamil Nadu',
            'shipping_postal_code' => '600001',
            'payment_method' => 'cod',
            'items' => [
                [
                    'product_variant_id' => $this->variant->id,
                    'quantity' => 1,
                ],
            ],
        ]);

        $response->assertStatus(200);

        Mail::assertSent(OrderPlacedNotificationMail::class, function ($mail) {
            return $mail->hasTo('primary@mayasree.com') &&
                   $mail->hasCc('warehouse@mayasree.com') &&
                   $mail->hasCc('manager@mayasree.com');
        });
    }

    public function test_order_placement_skips_notification_when_disabled_in_settings(): void
    {
        Mail::fake();

        // Notification explicitly disabled
        Setting::set('order_notification_enabled', false, 'email', 'boolean');
        Setting::set('primary_order_email', 'orders@mayasree.com', 'email', 'text');

        $response = $this->actingAs($this->customerUser)->postJson('/api/storefront/checkout', [
            'shipping_first_name' => 'Priya',
            'shipping_last_name' => 'Sundar',
            'shipping_phone' => '9876543210',
            'shipping_address_line_1' => 'No 45, Gandhi Road',
            'shipping_city' => 'Chennai',
            'shipping_state' => 'Tamil Nadu',
            'shipping_postal_code' => '600001',
            'payment_method' => 'cod',
            'items' => [
                [
                    'product_variant_id' => $this->variant->id,
                    'quantity' => 1,
                ],
            ],
        ]);

        $response->assertStatus(200);
        Mail::assertNothingSent();
    }

    public function test_order_placed_mailable_renders_detailed_product_breakdown_and_totals(): void
    {
        $order = Order::create([
            'user_id' => $this->customerUser->id,
            'order_number' => 'VIBE-TEST12345',
            'status' => 'order_placed',
            'payment_status' => 'paid',
            'payment_method' => 'cashfree',
            'payment_gateway' => 'cashfree',
            'subtotal' => 4998.00,
            'discount_amount' => 500.00,
            'shipping_amount' => 0.00,
            'grand_total' => 4498.00,
            'total_items' => 2,
            'shipping_first_name' => 'Ananya',
            'shipping_last_name' => 'Sharma',
            'shipping_phone' => '9876543210',
            'shipping_address_line_1' => 'Flat 402, Royal Palms',
            'shipping_city' => 'Bangalore',
            'shipping_state' => 'Karnataka',
            'shipping_postal_code' => '560001',
            'shipping_country' => 'India',
            'billing_first_name' => 'Ananya',
            'billing_last_name' => 'Sharma',
            'billing_phone' => '9876543210',
            'billing_address_line_1' => 'Flat 402, Royal Palms',
            'billing_city' => 'Bangalore',
            'billing_state' => 'Karnataka',
            'billing_postal_code' => '560001',
            'billing_country' => 'India',
        ]);

        OrderItem::create([
            'order_id' => $order->id,
            'product_id' => $this->variant->product_id,
            'product_variant_id' => $this->variant->id,
            'product_name' => 'Kanjivaram Silk Saree - Ruby Red',
            'variant_name' => 'Size: Free Size Color: Ruby Red',
            'sku' => 'KANJI-RUBY-01',
            'quantity' => 2,
            'unit_mrp' => 4999.00,
            'unit_price' => 2499.00,
            'total_price' => 4998.00,
        ]);

        $mailable = new OrderPlacedNotificationMail($order);
        $renderedHtml = $mailable->render();

        // Check key elements rendered in the email
        $this->assertStringContainsString('VIBE-TEST12345', $renderedHtml);
        $this->assertStringContainsString('Ananya Sharma', $renderedHtml);
        $this->assertStringContainsString('9876543210', $renderedHtml);
        $this->assertStringContainsString('Bangalore', $renderedHtml);
        $this->assertStringContainsString('Kanjivaram Silk Saree - Ruby Red', $renderedHtml);
        $this->assertStringContainsString('KANJI-RUBY-01', $renderedHtml);
        $this->assertStringContainsString('4,498.00', $renderedHtml);
        $this->assertStringContainsString('Online Payment', $renderedHtml);
        $this->assertStringContainsString('Maya Sree Fashion', $renderedHtml);
    }

    public function test_setting_api_can_update_and_retrieve_order_email_preferences(): void
    {
        $response = $this->actingAs($this->adminUser)->postJson('/api/admin/settings/batch', [
            'settings' => [
                'email' => [
                    'order_notification_enabled' => true,
                    'primary_order_email' => 'storealerts@mayasree.com',
                    'additional_order_emails' => 'ops@mayasree.com',
                    'smtp_host' => 'smtp.mailtrap.io',
                    'smtp_port' => 2525,
                ],
            ],
        ]);

        $response->assertStatus(200)
            ->assertJsonPath('success', true);

        $this->assertTrue(Setting::get('order_notification_enabled', 'email'));
        $this->assertEquals('storealerts@mayasree.com', Setting::get('primary_order_email', 'email'));
        $this->assertEquals('ops@mayasree.com', Setting::get('additional_order_emails', 'email'));

        // Verify retrieval via settings API
        $getIndex = $this->actingAs($this->adminUser)->getJson('/api/admin/settings');
        $getIndex->assertStatus(200)
            ->assertJsonPath('data.email.order_notification_enabled', true)
            ->assertJsonPath('data.email.primary_order_email', 'storealerts@mayasree.com');
    }

    public function test_admin_can_send_test_email(): void
    {
        Mail::fake();

        $response = $this->actingAs($this->adminUser)->postJson('/api/admin/settings/test-email', [
            'email' => 'tester@mayasree.com',
        ]);

        $response->assertStatus(200)
            ->assertJsonPath('success', true);

        Mail::assertSent(TestNotificationMail::class, function ($mail) {
            return $mail->hasTo('tester@mayasree.com');
        });
    }
}
