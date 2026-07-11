<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\OrderReturn;
use App\Models\ReturnItem;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ReturnApiTest extends TestCase
{
    use RefreshDatabase;

    protected $adminUser;
    protected $order;
    protected $orderItem;
    protected $variant;

    protected function setUp(): void
    {
        parent::setUp();

        $this->adminUser = User::factory()->create([
            'email' => 'admin@test.com',
        ]);

        $customer = User::factory()->create([
            'first_name' => 'John',
            'last_name' => 'Doe',
            'email' => 'john@test.com',
        ]);

        $category = Category::factory()->create();
        $product = Product::factory()->create([
            'category_id' => $category->id,
            'name' => 'Winter Jacket',
        ]);

        $this->variant = ProductVariant::factory()->create([
            'product_id' => $product->id,
            'sku' => 'JKT-WNT-01',
            'stock_quantity' => 5,
        ]);

        $this->order = Order::create([
            'user_id' => $customer->id,
            'status' => 'delivered',
            'payment_status' => 'paid',
            'payment_method' => 'upi',
            'subtotal' => 1200.00,
            'grand_total' => 1416.00,
            'total_items' => 1,
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

        $this->orderItem = OrderItem::create([
            'order_id' => $this->order->id,
            'product_id' => $product->id,
            'product_variant_id' => $this->variant->id,
            'product_name' => 'Winter Jacket',
            'sku' => 'JKT-WNT-01',
            'quantity' => 1,
            'unit_price' => 1200.00,
            'unit_mrp' => 1200.00,
            'total_price' => 1200.00,
        ]);
    }

    public function test_can_list_returns_with_filters(): void
    {
        $returnRequest = OrderReturn::create([
            'order_id' => $this->order->id,
            'user_id' => $this->order->user_id,
            'status' => 'requested',
            'reason' => 'wrong_size',
            'refund_amount' => 1200.00,
        ]);

        $response = $this->getJson('/api/admin/returns');
        $response->assertStatus(200)
            ->assertJsonPath('success', true)
            ->assertJsonCount(1, 'data');

        // Test search
        $response = $this->getJson('/api/admin/returns?search=RET-');
        $response->assertStatus(200)->assertJsonCount(1, 'data');

        // Test filter
        $response = $this->getJson('/api/admin/returns?status=requested');
        $response->assertStatus(200)->assertJsonCount(1, 'data');

        $response = $this->getJson('/api/admin/returns?status=refunded');
        $response->assertStatus(200)->assertJsonCount(0, 'data');
    }

    public function test_can_get_return_request_details(): void
    {
        $returnRequest = OrderReturn::create([
            'order_id' => $this->order->id,
            'user_id' => $this->order->user_id,
            'status' => 'requested',
            'reason' => 'wrong_size',
            'refund_amount' => 1200.00,
        ]);

        $response = $this->getJson("/api/admin/returns/{$returnRequest->id}");
        $response->assertStatus(200)
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.return_number', $returnRequest->return_number);
    }

    public function test_return_lifecycle_workflow_transitions(): void
    {
        $returnRequest = OrderReturn::create([
            'order_id' => $this->order->id,
            'user_id' => $this->order->user_id,
            'status' => 'requested',
            'reason' => 'defective',
        ]);

        $returnItem = ReturnItem::create([
            'return_id' => $returnRequest->id,
            'order_item_id' => $this->orderItem->id,
            'product_variant_id' => $this->variant->id,
            'quantity' => 1,
            'reason' => 'defective',
        ]);

        // 1. Approve
        $response = $this->putJson("/api/admin/returns/{$returnRequest->id}/status", [
            'status' => 'approved',
        ]);
        $response->assertStatus(200);
        $this->assertEquals('approved', $returnRequest->fresh()->status);

        // 2. Schedule Pickup
        $response = $this->putJson("/api/admin/returns/{$returnRequest->id}/status", [
            'status' => 'pickup_scheduled',
        ]);
        $response->assertStatus(200);
        $this->assertEquals('pickup_scheduled', $returnRequest->fresh()->status);

        // 3. Mark Picked Up
        $response = $this->putJson("/api/admin/returns/{$returnRequest->id}/status", [
            'status' => 'picked_up',
        ]);
        $response->assertStatus(200);
        $this->assertEquals('picked_up', $returnRequest->fresh()->status);

        // 4. Perform QC Check and Restock Variant
        $response = $this->putJson("/api/admin/returns/{$returnRequest->id}/status", [
            'status' => 'qc_passed',
            'qc_items' => [
                [
                    'item_id' => $returnItem->id,
                    'qc_status' => 'passed',
                    'qc_notes' => 'Tested okay.',
                ],
            ],
        ]);
        $response->assertStatus(200);

        // Verify variant stock quantity is restored from 5 ➔ 6
        $this->assertEquals(6, $this->variant->fresh()->stock_quantity);
        $this->assertDatabaseHas('inventory_ledger', [
            'product_variant_id' => $this->variant->id,
            'type' => 'RETURN',
            'direction' => 'IN',
            'quantity' => 1,
        ]);
    }

    public function test_can_reject_return_with_reason(): void
    {
        $returnRequest = OrderReturn::create([
            'order_id' => $this->order->id,
            'user_id' => $this->order->user_id,
            'status' => 'requested',
            'reason' => 'wrong_size',
        ]);

        $response = $this->putJson("/api/admin/returns/{$returnRequest->id}/status", [
            'status' => 'rejected',
            'rejection_reason' => 'Past 30 days return window limits.',
        ]);

        $response->assertStatus(200);
        $this->assertEquals('rejected', $returnRequest->fresh()->status);
        $this->assertEquals('Past 30 days return window limits.', $returnRequest->fresh()->rejection_reason);
    }

    public function test_can_issue_refund_and_close_return(): void
    {
        $returnRequest = OrderReturn::create([
            'order_id' => $this->order->id,
            'user_id' => $this->order->user_id,
            'status' => 'approved',
            'reason' => 'wrong_size',
            'refund_amount' => 1200.00,
        ]);

        $response = $this->postJson("/api/admin/returns/{$returnRequest->id}/refund", [
            'amount' => 1200.00,
            'reason' => 'QC approved partial refund payment.',
        ]);

        $response->assertStatus(200)
            ->assertJsonPath('success', true);

        $this->assertEquals('refunded', $returnRequest->fresh()->status);
        $this->assertEquals('returned', $this->order->fresh()->status);
        $this->assertEquals('refunded', $this->order->fresh()->payment_status);

        $this->assertDatabaseHas('refunds', [
            'return_id' => $returnRequest->id,
            'amount' => 1200.00,
            'status' => 'processed',
        ]);
    }

    public function test_can_add_admin_comments(): void
    {
        $returnRequest = OrderReturn::create([
            'order_id' => $this->order->id,
            'user_id' => $this->order->user_id,
            'status' => 'requested',
            'reason' => 'wrong_size',
        ]);

        $response = $this->actingAs($this->adminUser)->postJson("/api/admin/returns/{$returnRequest->id}/notes", [
            'note' => 'Staff call customer to verify issues.',
        ]);

        $response->assertStatus(200);
        $this->assertStringContainsString('Staff call customer to verify issues.', $returnRequest->fresh()->admin_notes);
    }
}
