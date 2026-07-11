<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\PurchaseOrder;
use App\Models\PurchaseOrderItem;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PurchaseOrderApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_list_purchase_orders(): void
    {
        PurchaseOrder::factory()->create([
            'supplier_name' => 'Supplier Alpha',
            'status' => 'draft',
            'total_amount' => 1000.00
        ]);

        PurchaseOrder::factory()->create([
            'supplier_name' => 'Supplier Beta',
            'status' => 'ordered',
            'total_amount' => 2500.00
        ]);

        $response = $this->getJson('/api/admin/purchase-orders');
        $response->assertStatus(200)
            ->assertJsonCount(2, 'data');

        $response = $this->getJson('/api/admin/purchase-orders?status=ordered');
        $response->assertStatus(200)
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.supplier_name', 'Supplier Beta');
    }

    public function test_can_create_purchase_order_draft_and_calculate_total(): void
    {
        $variant = ProductVariant::factory()->create(['sku' => 'VAR-1']);

        $payload = [
            'supplier_name' => 'Global Garments',
            'supplier_contact' => 'contact@globalgarments.com',
            'notes' => 'Urgent replenishment batch',
            'items' => [
                [
                    'product_variant_id' => $variant->id,
                    'quantity_ordered' => 100,
                    'unit_cost' => 150.00
                ]
            ]
        ];

        $response = $this->postJson('/api/admin/purchase-orders', $payload);

        $response->assertStatus(201)
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.supplier_name', 'Global Garments')
            ->assertJsonPath('data.status', 'draft')
            ->assertJsonPath('data.total_amount', '15000.00'); // 100 * 150

        $this->assertDatabaseHas('purchase_orders', [
            'supplier_name' => 'Global Garments',
            'status' => 'draft',
            'total_amount' => 15000.00
        ]);

        $this->assertDatabaseHas('purchase_order_items', [
            'product_variant_id' => $variant->id,
            'quantity_ordered' => 100,
            'unit_cost' => 150.00,
            'total_cost' => 15000.00
        ]);
    }

    public function test_can_update_draft_purchase_order(): void
    {
        $po = PurchaseOrder::factory()->create(['supplier_name' => 'Old Supplier', 'status' => 'draft']);
        $variant = ProductVariant::factory()->create();

        $payload = [
            'supplier_name' => 'New Supplier',
            'items' => [
                [
                    'product_variant_id' => $variant->id,
                    'quantity_ordered' => 50,
                    'unit_cost' => 200.00
                ]
            ]
        ];

        $response = $this->putJson("/api/admin/purchase-orders/{$po->id}", $payload);

        $response->assertStatus(200)
            ->assertJsonPath('data.supplier_name', 'New Supplier')
            ->assertJsonPath('data.total_amount', '10000.00');

        $this->assertDatabaseHas('purchase_orders', [
            'id' => $po->id,
            'supplier_name' => 'New Supplier',
            'total_amount' => 10000.00
        ]);
    }

    public function test_cannot_update_non_draft_purchase_order(): void
    {
        $po = PurchaseOrder::factory()->create(['supplier_name' => 'Static PO', 'status' => 'ordered']);

        $payload = [
            'supplier_name' => 'Illegal Rename'
        ];

        $response = $this->putJson("/api/admin/purchase-orders/{$po->id}", $payload);

        $response->assertStatus(400)
            ->assertJsonPath('success', false);
    }

    public function test_can_receive_po_stock_and_update_inventory(): void
    {
        $variant = ProductVariant::factory()->create(['sku' => 'RECV-1', 'stock_quantity' => 10]);
        $po = PurchaseOrder::factory()->create(['supplier_name' => 'Direct Supplier', 'status' => 'ordered']);
        $poItem = PurchaseOrderItem::create([
            'purchase_order_id' => $po->id,
            'product_variant_id' => $variant->id,
            'quantity_ordered' => 50,
            'quantity_received' => 0,
            'unit_cost' => 100.00,
            'total_cost' => 5000.00
        ]);

        $payload = [
            'items' => [
                [
                    'item_id' => $poItem->id,
                    'quantity_received' => 20
                ]
            ]
        ];

        $response = $this->postJson("/api/admin/purchase-orders/{$po->id}/receive", $payload);

        $response->assertStatus(200)
            ->assertJsonPath('data.status', 'partial'); // partial received

        // Verify variant stock was incremented by 20 (10 original + 20 received = 30)
        $this->assertDatabaseHas('product_variants', [
            'id' => $variant->id,
            'stock_quantity' => 30
        ]);

        // Verify PO item received quantity updated
        $this->assertDatabaseHas('purchase_order_items', [
            'id' => $poItem->id,
            'quantity_received' => 20
        ]);

        // Verify ledger entry IN
        $this->assertDatabaseHas('inventory_ledger', [
            'product_variant_id' => $variant->id,
            'type' => 'PURCHASE',
            'direction' => 'IN',
            'quantity' => 20,
            'stock_before' => 10,
            'stock_after' => 30
        ]);
    }

    public function test_can_delete_draft_purchase_order(): void
    {
        $po = PurchaseOrder::factory()->create(['status' => 'draft']);

        $response = $this->deleteJson("/api/admin/purchase-orders/{$po->id}");
        $response->assertStatus(200);

        $this->assertDatabaseMissing('purchase_orders', ['id' => $po->id]);
    }

    public function test_cannot_delete_ordered_purchase_order(): void
    {
        $po = PurchaseOrder::factory()->create(['status' => 'ordered']);

        $response = $this->deleteJson("/api/admin/purchase-orders/{$po->id}");
        $response->assertStatus(400);

        $this->assertDatabaseHas('purchase_orders', ['id' => $po->id]);
    }
}
