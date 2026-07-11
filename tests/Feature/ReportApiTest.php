<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\User;
use App\Models\CustomerProfile;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ReportApiTest extends TestCase
{
    use RefreshDatabase;

    protected $adminUser;

    protected function setUp(): void
    {
        parent::setUp();

        $this->adminUser = User::factory()->create([
            'email' => 'admin@test.com',
        ]);

        $customer = User::factory()->create();
        $customer->customerProfile()->create([
            'total_spent' => 2000.00,
            'total_orders' => 2,
        ]);

        $category = Category::factory()->create(['name' => 'Dresses']);
        $product = Product::factory()->create([
            'category_id' => $category->id,
            'name' => 'Maxi Dress',
        ]);

        $variant1 = ProductVariant::factory()->create([
            'product_id' => $product->id,
            'sku' => 'MAXI-DRS-01',
            'stock_quantity' => 10,
            'cost_price' => 500.00,
            'selling_price' => 1000.00,
        ]);

        $variant2 = ProductVariant::factory()->create([
            'product_id' => $product->id,
            'sku' => 'MAXI-DRS-02',
            'stock_quantity' => 2, // low stock (threshold is 5)
            'low_stock_threshold' => 5,
            'cost_price' => 450.00,
            'selling_price' => 900.00,
        ]);

        $order = Order::create([
            'user_id' => $customer->id,
            'status' => 'delivered',
            'payment_status' => 'paid',
            'payment_method' => 'upi',
            'subtotal' => 1900.00,
            'grand_total' => 2242.00,
            'total_items' => 2,
            'shipping_first_name' => 'John',
            'shipping_last_name' => 'Doe',
            'shipping_phone' => '1234567890',
            'shipping_address_line_1' => 'Street 1',
            'shipping_city' => 'Mumbai',
            'shipping_state' => 'Maharashtra',
            'shipping_postal_code' => '400001',
            'billing_first_name' => 'John',
            'billing_last_name' => 'Doe',
            'billing_phone' => '1234567890',
            'billing_address_line_1' => 'Street 1',
            'billing_city' => 'Mumbai',
            'billing_state' => 'Maharashtra',
            'billing_postal_code' => '400001',
        ]);

        OrderItem::create([
            'order_id' => $order->id,
            'product_id' => $product->id,
            'product_variant_id' => $variant1->id,
            'product_name' => 'Maxi Dress S',
            'sku' => 'MAXI-DRS-01',
            'quantity' => 1,
            'unit_price' => 1000.00,
            'unit_mrp' => 1000.00,
            'total_price' => 1000.00,
        ]);

        OrderItem::create([
            'order_id' => $order->id,
            'product_id' => $product->id,
            'product_variant_id' => $variant2->id,
            'product_name' => 'Maxi Dress M',
            'sku' => 'MAXI-DRS-02',
            'quantity' => 1,
            'unit_price' => 900.00,
            'unit_mrp' => 900.00,
            'total_price' => 900.00,
        ]);
    }

    public function test_can_retrieve_sales_reports(): void
    {
        $response = $this->getJson('/api/admin/reports/sales');

        $response->assertStatus(200)
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.kpis.revenue', 2242)
            ->assertJsonPath('data.kpis.orders', 1)
            ->assertJsonPath('data.kpis.units_sold', 2)
            ->assertJsonCount(1, 'data.revenue_trend')
            ->assertJsonPath('data.category_splits.0.category', 'Dresses')
            ->assertJsonPath('data.payment_splits.0.method', 'UPI')
            ->assertJsonPath('data.top_products.0.sku', 'MAXI-DRS-01');
    }

    public function test_can_retrieve_inventory_reports(): void
    {
        $response = $this->getJson('/api/admin/reports/inventory');

        // valuation: 10 * 500 + 2 * 450 = 5000 + 900 = 5900
        $response->assertStatus(200)
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.kpis.total_variants', 2)
            ->assertJsonPath('data.kpis.low_stock', 1)
            ->assertJsonPath('data.kpis.valuation', 5900)
            ->assertJsonCount(1, 'data.low_stock_details');
    }

    public function test_can_retrieve_customer_reports(): void
    {
        $response = $this->getJson('/api/admin/reports/customers');

        $response->assertStatus(200)
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.kpis.total_customers', 1)
            ->assertJsonPath('data.kpis.repeat_rate', 100)
            ->assertJsonPath('data.kpis.average_ltv', 2000);
    }
}
