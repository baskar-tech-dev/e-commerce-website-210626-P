<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\CustomerProfile;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\PurchaseOrder;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DashboardApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_retrieve_dashboard_stats(): void
    {
        // 1. Seed Categories
        $category = Category::factory()->create(['name' => 'Shirts']);

        // 2. Seed Customer profiles
        $user = User::factory()->create();
        $user->customerProfile()->create([
            'total_spent' => 500.00,
            'total_orders' => 2,
        ]);

        // 3. Seed Products and Low Stock variants
        $product = Product::factory()->create([
            'category_id' => $category->id,
            'name' => 'Denim Shirt'
        ]);

        // Low stock variant (stock = 2, threshold = 5)
        ProductVariant::factory()->create([
            'product_id' => $product->id,
            'stock_quantity' => 2,
            'low_stock_threshold' => 5,
        ]);

        // Healthy stock variant (stock = 20, threshold = 5)
        ProductVariant::factory()->create([
            'product_id' => $product->id,
            'stock_quantity' => 20,
            'low_stock_threshold' => 5,
        ]);

        // 4. Seed Customer Order
        $order = \App\Models\Order::create([
            'user_id' => $user->id,
            'status' => 'delivered',
            'payment_status' => 'paid',
            'subtotal' => 1000.00,
            'grand_total' => 1180.00,
            'total_items' => 1,
            'shipping_first_name' => 'Direct',
            'shipping_last_name' => 'Supplier',
            'shipping_phone' => '1234567890',
            'shipping_address_line_1' => 'Street 1',
            'shipping_city' => 'Mumbai',
            'shipping_state' => 'Maharashtra',
            'shipping_postal_code' => '400001',
            'billing_first_name' => 'Direct',
            'billing_last_name' => 'Supplier',
            'billing_phone' => '1234567890',
            'billing_address_line_1' => 'Street 1',
            'billing_city' => 'Mumbai',
            'billing_state' => 'Maharashtra',
            'billing_postal_code' => '400001',
        ]);

        // Call the dashboard stats endpoint
        $response = $this->getJson('/api/admin/dashboard/stats');

        $response->assertStatus(200)
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.kpis.customers.value', '1')
            ->assertJsonPath('data.kpis.orders.value', '1');

        // Check low stock variant alert was returned
        $response->assertJsonCount(1, 'data.low_stock_alerts');
        $response->assertJsonPath('data.low_stock_alerts.0.product_name', 'Denim Shirt');

        // Check recent orders lists the customer order
        $response->assertJsonCount(1, 'data.recent_orders');
        $response->assertJsonPath('data.recent_orders.0.customer_name', 'Direct Supplier');
    }
}
