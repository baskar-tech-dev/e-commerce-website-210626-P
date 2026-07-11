<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OrderApiTest extends TestCase
{
    use RefreshDatabase;

    protected $user;
    protected $variant;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create([
            'first_name' => 'Alice',
            'last_name' => 'Tester',
            'email' => 'alice@test.com'
        ]);

        $category = Category::factory()->create();
        $product = Product::factory()->create([
            'category_id' => $category->id,
            'name' => 'Polyester Polo'
        ]);

        $this->variant = ProductVariant::factory()->create([
            'product_id' => $product->id,
            'sku' => 'POLO-POLY-01',
            'stock_quantity' => 10,
        ]);
    }

    public function test_can_list_orders_with_filters_and_search(): void
    {
        $order = Order::create([
            'user_id' => $this->user->id,
            'status' => 'pending',
            'payment_status' => 'pending',
            'subtotal' => 1000.00,
            'grand_total' => 1180.00,
            'total_items' => 1,
            'shipping_first_name' => 'Alice',
            'shipping_last_name' => 'Tester',
            'shipping_phone' => '1234567890',
            'shipping_address_line_1' => 'Street 1',
            'shipping_city' => 'Mumbai',
            'shipping_state' => 'Maharashtra',
            'shipping_postal_code' => '400001',
            'billing_first_name' => 'Alice',
            'billing_last_name' => 'Tester',
            'billing_phone' => '1234567890',
            'billing_address_line_1' => 'Street 1',
            'billing_city' => 'Mumbai',
            'billing_state' => 'Maharashtra',
            'billing_postal_code' => '400001',
        ]);

        $response = $this->getJson('/api/admin/orders');
        $response->assertStatus(200)
            ->assertJsonPath('success', true)
            ->assertJsonCount(1, 'data');

        // Test search filter
        $response = $this->getJson('/api/admin/orders?search=Alice');
        $response->assertStatus(200)->assertJsonCount(1, 'data');

        // Test non-matching search filter
        $response = $this->getJson('/api/admin/orders?search=Bob');
        $response->assertStatus(200)->assertJsonCount(0, 'data');
    }

    public function test_can_get_order_details(): void
    {
        $order = Order::create([
            'user_id' => $this->user->id,
            'status' => 'pending',
            'payment_status' => 'pending',
            'subtotal' => 1000.00,
            'grand_total' => 1180.00,
            'total_items' => 1,
            'shipping_first_name' => 'Alice',
            'shipping_last_name' => 'Tester',
            'shipping_phone' => '1234567890',
            'shipping_address_line_1' => 'Street 1',
            'shipping_city' => 'Mumbai',
            'shipping_state' => 'Maharashtra',
            'shipping_postal_code' => '400001',
            'billing_first_name' => 'Alice',
            'billing_last_name' => 'Tester',
            'billing_phone' => '1234567890',
            'billing_address_line_1' => 'Street 1',
            'billing_city' => 'Mumbai',
            'billing_state' => 'Maharashtra',
            'billing_postal_code' => '400001',
        ]);

        $response = $this->getJson("/api/admin/orders/{$order->id}");
        $response->assertStatus(200)
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.order_number', $order->order_number);
    }

    public function test_order_status_transitions_and_inventory_ledger_integration(): void
    {
        $order = Order::create([
            'user_id' => $this->user->id,
            'status' => 'pending',
            'payment_status' => 'pending',
            'subtotal' => 1000.00,
            'grand_total' => 1180.00,
            'total_items' => 1,
            'shipping_first_name' => 'Alice',
            'shipping_last_name' => 'Tester',
            'shipping_phone' => '1234567890',
            'shipping_address_line_1' => 'Street 1',
            'shipping_city' => 'Mumbai',
            'shipping_state' => 'Maharashtra',
            'shipping_postal_code' => '400001',
            'billing_first_name' => 'Alice',
            'billing_last_name' => 'Tester',
            'billing_phone' => '1234567890',
            'billing_address_line_1' => 'Street 1',
            'billing_city' => 'Mumbai',
            'billing_state' => 'Maharashtra',
            'billing_postal_code' => '400001',
        ]);

        OrderItem::create([
            'order_id' => $order->id,
            'product_id' => $this->variant->product_id,
            'product_variant_id' => $this->variant->id,
            'product_name' => 'Polyester Polo',
            'sku' => $this->variant->sku,
            'quantity' => 2,
            'unit_price' => 500.00,
            'unit_mrp' => 500.00,
            'total_price' => 1000.00,
        ]);

        // 1. Move pending -> confirmed
        $response = $this->putJson("/api/admin/orders/{$order->id}/status", [
            'status' => 'confirmed'
        ]);
        $response->assertStatus(200);
        $this->assertEquals('confirmed', $order->fresh()->status);

        // 2. Move confirmed -> processing (should trigger ledger OUT decrement of 2 units)
        $response = $this->putJson("/api/admin/orders/{$order->id}/status", [
            'status' => 'processing'
        ]);
        $response->assertStatus(200);

        // Check variant stock decreased from 10 -> 8
        $this->assertEquals(8, $this->variant->fresh()->stock_quantity);
        $this->assertDatabaseHas('inventory_ledger', [
            'product_variant_id' => $this->variant->id,
            'type' => 'SALE',
            'direction' => 'OUT',
            'quantity' => 2,
            'reference_type' => 'Order',
            'reference_id' => $order->id,
        ]);

        // 3. Move processing -> cancelled (should return stock, e.g. IN ledger of 2 units)
        $response = $this->putJson("/api/admin/orders/{$order->id}/status", [
            'status' => 'cancelled'
        ]);
        $response->assertStatus(200);

        // Check variant stock returned to 10
        $this->assertEquals(10, $this->variant->fresh()->stock_quantity);
        $this->assertDatabaseHas('inventory_ledger', [
            'product_variant_id' => $this->variant->id,
            'type' => 'RETURN',
            'direction' => 'IN',
            'quantity' => 2,
        ]);
    }

    public function test_can_add_administrative_comment(): void
    {
        $order = Order::create([
            'user_id' => $this->user->id,
            'status' => 'pending',
            'payment_status' => 'pending',
            'subtotal' => 1000.00,
            'grand_total' => 1180.00,
            'total_items' => 1,
            'shipping_first_name' => 'Alice',
            'shipping_last_name' => 'Tester',
            'shipping_phone' => '1234567890',
            'shipping_address_line_1' => 'Street 1',
            'shipping_city' => 'Mumbai',
            'shipping_state' => 'Maharashtra',
            'shipping_postal_code' => '400001',
            'billing_first_name' => 'Alice',
            'billing_last_name' => 'Tester',
            'billing_phone' => '1234567890',
            'billing_address_line_1' => 'Street 1',
            'billing_city' => 'Mumbai',
            'billing_state' => 'Maharashtra',
            'billing_postal_code' => '400001',
        ]);

        $response = $this->postJson("/api/admin/orders/{$order->id}/notes", [
            'note' => 'Test admin note'
        ]);

        $response->assertStatus(200)
            ->assertJsonPath('success', true);

        $this->assertStringContainsString('Test admin note', $order->fresh()->admin_notes);
    }
}
