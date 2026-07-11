<?php

namespace Tests\Feature;

use App\Models\Coupon;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CouponApiTest extends TestCase
{
    use RefreshDatabase;

    protected $adminUser;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->adminUser = User::factory()->create([
            'name' => 'Admin User',
            'email' => 'admin@test.com',
        ]);
    }

    public function test_can_list_coupons_with_filters(): void
    {
        Coupon::create([
            'code' => 'TESTPERCENT',
            'name' => 'Test 10%',
            'type' => 'percentage',
            'value' => 10.00,
            'max_uses_per_user' => 1,
            'first_order_only' => false,
            'is_auto_apply' => false,
            'is_combinable' => false,
            'is_active' => true,
            'starts_at' => now()->subDays(5),
            'expires_at' => now()->addDays(5),
        ]);

        Coupon::create([
            'code' => 'TESTFLAT',
            'name' => 'Test Flat 100',
            'type' => 'flat',
            'value' => 100.00,
            'max_uses_per_user' => 1,
            'first_order_only' => false,
            'is_auto_apply' => false,
            'is_combinable' => false,
            'is_active' => false, // Inactive
            'starts_at' => now()->subDays(5),
            'expires_at' => now()->addDays(5),
        ]);

        // Get all
        $response = $this->getJson('/api/admin/coupons');
        $response->assertStatus(200)
            ->assertJsonPath('success', true)
            ->assertJsonCount(2, 'data');

        // Filter active
        $response = $this->getJson('/api/admin/coupons?status=active');
        $response->assertStatus(200)
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.code', 'TESTPERCENT');

        // Filter inactive
        $response = $this->getJson('/api/admin/coupons?status=inactive');
        $response->assertStatus(200)
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.code', 'TESTFLAT');
    }

    public function test_can_create_coupon(): void
    {
        $response = $this->actingAs($this->adminUser)->postJson('/api/admin/coupons', [
            'code' => 'new-year-50', // lower case, observer should uppercase it
            'name' => 'New Year 50% Off',
            'type' => 'percentage',
            'value' => 50.00,
            'max_uses_per_user' => 1,
            'starts_at' => now()->format('Y-m-d'),
            'expires_at' => now()->addDays(10)->format('Y-m-d'),
            'is_active' => true,
            'first_order_only' => true,
            'is_auto_apply' => false,
            'is_combinable' => false,
        ]);

        $response->assertStatus(201)
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.code', 'NEW-YEAR-50'); // Assert capitalized code

        $this->assertDatabaseHas('coupons', [
            'code' => 'NEW-YEAR-50',
            'first_order_only' => true,
        ]);
    }

    public function test_cannot_create_duplicate_coupon_code(): void
    {
        Coupon::create([
            'code' => 'DUPLICATE',
            'name' => 'First Coupon',
            'type' => 'percentage',
            'value' => 10.00,
            'max_uses_per_user' => 1,
            'first_order_only' => false,
            'is_auto_apply' => false,
            'is_combinable' => false,
            'is_active' => true,
            'starts_at' => now(),
        ]);

        $response = $this->postJson('/api/admin/coupons', [
            'code' => 'duplicate', // Should clash even with case difference
            'name' => 'Second Coupon',
            'type' => 'percentage',
            'value' => 20.00,
            'max_uses_per_user' => 1,
            'starts_at' => now()->format('Y-m-d'),
            'is_active' => true,
            'first_order_only' => false,
            'is_auto_apply' => false,
            'is_combinable' => false,
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['code']);
    }

    public function test_can_update_coupon(): void
    {
        $coupon = Coupon::create([
            'code' => 'ORIGINAL',
            'name' => 'Original Name',
            'type' => 'percentage',
            'value' => 15.00,
            'max_uses_per_user' => 1,
            'first_order_only' => false,
            'is_auto_apply' => false,
            'is_combinable' => false,
            'is_active' => true,
            'starts_at' => now(),
        ]);

        $response = $this->putJson("/api/admin/coupons/{$coupon->id}", [
            'code' => 'original', // keep same code (checks validation exception for self)
            'name' => 'Updated Name',
            'type' => 'percentage',
            'value' => 20.00,
            'max_uses_per_user' => 2,
            'starts_at' => now()->format('Y-m-d'),
            'is_active' => false,
            'first_order_only' => true,
            'is_auto_apply' => true,
            'is_combinable' => true,
        ]);

        $response->assertStatus(200);
        $this->assertEquals('Updated Name', $coupon->fresh()->name);
        $this->assertEquals(20.00, $coupon->fresh()->value);
        $this->assertFalse($coupon->fresh()->is_active);
    }

    public function test_can_delete_coupon(): void
    {
        $coupon = Coupon::create([
            'code' => 'DELETE-ME',
            'name' => 'Discard Coupon',
            'type' => 'flat',
            'value' => 5.00,
            'max_uses_per_user' => 1,
            'first_order_only' => false,
            'is_auto_apply' => false,
            'is_combinable' => false,
            'is_active' => true,
            'starts_at' => now(),
        ]);

        $response = $this->deleteJson("/api/admin/coupons/{$coupon->id}");
        $response->assertStatus(200);

        $this->assertSoftDeleted('coupons', ['id' => $coupon->id]);
    }
}
