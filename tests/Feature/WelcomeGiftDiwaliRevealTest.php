<?php

namespace Tests\Feature;

use App\Models\Coupon;
use App\Models\Setting;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class WelcomeGiftDiwaliRevealTest extends TestCase
{
    use RefreshDatabase;

    protected $adminUser;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->adminUser = User::factory()->create([
            'name' => 'Admin User',
            'email' => 'admin@mayasree.com',
        ]);
    }

    public function test_storefront_returns_welcome_gift_with_diwali_reveal_active_and_valid_coupon(): void
    {
        $coupon = Coupon::create([
            'code' => 'MSFDIWALI10',
            'name' => 'Diwali 10% Festival Offer',
            'description' => 'Festive special 10% off',
            'type' => 'percentage',
            'value' => 10.00,
            'min_order_value' => 499.00,
            'max_discount' => 500.00,
            'max_uses_per_user' => 1,
            'first_order_only' => false,
            'is_auto_apply' => false,
            'is_combinable' => false,
            'is_active' => true,
            'starts_at' => now()->subDay(),
            'expires_at' => now()->addDays(7),
        ]);

        Setting::set('is_enabled', true, 'welcome_gift', 'boolean');
        Setting::set('diwali_reveal_enabled', true, 'welcome_gift', 'boolean');
        Setting::set('coupon_code', 'MSFDIWALI10', 'welcome_gift', 'text');
        Setting::set('discount_text', 'Enjoy 10% OFF Your Diwali Order', 'welcome_gift', 'text');

        $response = $this->getJson('/api/storefront/welcome-gift');

        $response->assertStatus(200)
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.is_enabled', true)
            ->assertJsonPath('data.diwali_reveal_enabled', true)
            ->assertJsonPath('data.coupon_code', 'MSFDIWALI10')
            ->assertJsonPath('data.is_coupon_valid', true)
            ->assertJsonPath('data.coupon.code', 'MSFDIWALI10')
            ->assertJsonPath('data.coupon.value', 10)
            ->assertJsonPath('data.coupon.min_order_value', 499)
            ->assertJsonPath('data.cta_text', 'REVEAL MY OFFER');
    }

    public function test_storefront_returns_invalid_when_coupon_is_expired(): void
    {
        Coupon::create([
            'code' => 'EXPIREDCOUPON',
            'name' => 'Expired Offer',
            'type' => 'percentage',
            'value' => 15.00,
            'max_uses_per_user' => 1,
            'first_order_only' => false,
            'is_auto_apply' => false,
            'is_combinable' => false,
            'is_active' => true,
            'starts_at' => now()->subDays(10),
            'expires_at' => now()->subDay(), // Expired
        ]);

        Setting::set('is_enabled', true, 'welcome_gift', 'boolean');
        Setting::set('diwali_reveal_enabled', true, 'welcome_gift', 'boolean');
        Setting::set('coupon_code', 'EXPIREDCOUPON', 'welcome_gift', 'text');

        $response = $this->getJson('/api/storefront/welcome-gift');

        $response->assertStatus(200)
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.is_coupon_valid', false)
            ->assertJsonPath('data.invalidation_reason', 'expired')
            ->assertJsonPath('data.coupon', null);
    }

    public function test_storefront_returns_invalid_when_coupon_usage_limit_reached(): void
    {
        Coupon::create([
            'code' => 'MAXEDOUT',
            'name' => 'Limited Coupon',
            'type' => 'flat',
            'value' => 100.00,
            'max_uses_total' => 5,
            'times_used' => 5, // Limit reached
            'max_uses_per_user' => 1,
            'first_order_only' => false,
            'is_auto_apply' => false,
            'is_combinable' => false,
            'is_active' => true,
            'starts_at' => now()->subDays(2),
        ]);

        Setting::set('is_enabled', true, 'welcome_gift', 'boolean');
        Setting::set('coupon_code', 'MAXEDOUT', 'welcome_gift', 'text');

        $response = $this->getJson('/api/storefront/welcome-gift');

        $response->assertStatus(200)
            ->assertJsonPath('data.is_coupon_valid', false)
            ->assertJsonPath('data.invalidation_reason', 'usage_limit_reached')
            ->assertJsonPath('data.coupon', null);
    }

    public function test_storefront_fallback_when_diwali_reveal_is_disabled(): void
    {
        Coupon::create([
            'code' => 'SAVE5',
            'name' => 'Standard Welcome',
            'type' => 'percentage',
            'value' => 5.00,
            'max_uses_per_user' => 1,
            'first_order_only' => true,
            'is_auto_apply' => false,
            'is_combinable' => false,
            'is_active' => true,
            'starts_at' => now()->subDay(),
        ]);

        Setting::set('is_enabled', true, 'welcome_gift', 'boolean');
        Setting::set('diwali_reveal_enabled', false, 'welcome_gift', 'boolean');
        Setting::set('coupon_code', 'SAVE5', 'welcome_gift', 'text');

        $response = $this->getJson('/api/storefront/welcome-gift');

        $response->assertStatus(200)
            ->assertJsonPath('data.diwali_reveal_enabled', false)
            ->assertJsonPath('data.cta_text', 'Open Gift')
            ->assertJsonPath('data.is_coupon_valid', true);
    }

    public function test_admin_settings_batch_update_warns_for_invalid_coupon(): void
    {
        Coupon::create([
            'code' => 'OLDEXPIRED',
            'name' => 'Old Expired',
            'type' => 'flat',
            'value' => 50.00,
            'max_uses_per_user' => 1,
            'first_order_only' => false,
            'is_auto_apply' => false,
            'is_combinable' => false,
            'is_active' => true,
            'starts_at' => now()->subDays(20),
            'expires_at' => now()->subDays(5),
        ]);

        $response = $this->postJson('/api/admin/settings/batch', [
            'settings' => [
                'welcome_gift' => [
                    'is_enabled' => true,
                    'diwali_reveal_enabled' => true,
                    'coupon_code' => 'OLDEXPIRED',
                ],
            ],
        ]);

        $response->assertStatus(200)
            ->assertJsonPath('success', true);

        $this->assertNotEmpty($response->json('warnings'));
        $this->assertTrue(Setting::get('diwali_reveal_enabled', 'welcome_gift'));
    }
}
