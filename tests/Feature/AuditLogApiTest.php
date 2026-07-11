<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Product;
use App\Models\Category;
use App\Models\AuditLog;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuditLogApiTest extends TestCase
{
    use RefreshDatabase;

    protected $adminUser;
    protected $category;

    protected function setUp(): void
    {
        parent::setUp();

        $this->adminUser = User::factory()->create([
            'email' => 'admin@test.com',
        ]);

        $this->category = Category::create([
            'name' => 'Shirts',
            'slug' => 'shirts',
            'is_active' => true,
        ]);
    }

    public function test_creating_and_updating_product_records_audit_logs(): void
    {
        $this->actingAs($this->adminUser);

        // 1. Create product (triggers 'created' event)
        $product = Product::create([
            'category_id' => $this->category->id,
            'name' => 'Epic Sweatshirt',
            'slug' => 'epic-sweatshirt',
            'mrp' => 1999.00,
            'selling_price' => 1499.00,
            'is_active' => true,
        ]);

        $this->assertDatabaseHas('audit_logs', [
            'auditable_type' => Product::class,
            'auditable_id' => $product->id,
            'action' => 'create',
            'user_id' => $this->adminUser->id,
        ]);

        // 2. Update product price (triggers 'updated' event)
        $product->update([
            'selling_price' => 1299.00,
        ]);

        $this->assertDatabaseHas('audit_logs', [
            'auditable_type' => Product::class,
            'auditable_id' => $product->id,
            'action' => 'update',
        ]);

        // Verify the values diff is serialized correctly
        $updateLog = AuditLog::where('action', 'update')
            ->where('auditable_id', $product->id)
            ->first();

        $this->assertEquals(1499.00, $updateLog->old_values['selling_price']);
        $this->assertEquals(1299.00, $updateLog->new_values['selling_price']);
    }

    public function test_can_retrieve_paginated_audit_logs_via_api(): void
    {
        $this->actingAs($this->adminUser);

        // Seed manual logs
        AuditLog::create([
            'user_id' => $this->adminUser->id,
            'auditable_type' => Product::class,
            'auditable_id' => 45,
            'action' => 'create',
            'old_values' => null,
            'new_values' => ['name' => 'Demo Shirt'],
            'ip_address' => '127.0.0.1',
        ]);

        $response = $this->getJson('/api/admin/audit-logs');

        $response->assertStatus(200)
            ->assertJsonPath('success', true)
            ->assertJsonCount(1, 'data');
    }
}
