<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\InventoryLedger;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class InventoryApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_list_inventory_with_filters(): void
    {
        $category = Category::factory()->create(['name' => 'Clothing']);
        $product = Product::factory()->create([
            'category_id' => $category->id,
            'name' => 'Cotton Polo Shirt'
        ]);

        // OK Stock variant
        $variantOk = ProductVariant::factory()->create([
            'product_id' => $product->id,
            'sku' => 'POLO-S-WHT',
            'stock_quantity' => 50,
            'low_stock_threshold' => 5
        ]);

        // Low Stock variant
        $variantLow = ProductVariant::factory()->create([
            'product_id' => $product->id,
            'sku' => 'POLO-M-WHT',
            'stock_quantity' => 3,
            'low_stock_threshold' => 5
        ]);

        // Out of Stock variant
        $variantOut = ProductVariant::factory()->create([
            'product_id' => $product->id,
            'sku' => 'POLO-L-WHT',
            'stock_quantity' => 0,
            'low_stock_threshold' => 5
        ]);

        // 1. List all variants
        $response = $this->getJson('/api/admin/inventory');
        $response->assertStatus(200)
            ->assertJsonPath('success', true)
            ->assertJsonCount(3, 'data');

        // 2. Filter by status 'low'
        $response = $this->getJson('/api/admin/inventory?status=low');
        $response->assertStatus(200)
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.sku', 'POLO-M-WHT');

        // 3. Filter by status 'out_of_stock'
        $response = $this->getJson('/api/admin/inventory?status=out_of_stock');
        $response->assertStatus(200)
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.sku', 'POLO-L-WHT');

        // 4. Search by SKU
        $response = $this->getJson('/api/admin/inventory?search=POLO-S');
        $response->assertStatus(200)
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.sku', 'POLO-S-WHT');
    }

    public function test_can_adjust_stock_in_and_record_ledger(): void
    {
        $variant = ProductVariant::factory()->create([
            'sku' => 'TEST-SKU',
            'stock_quantity' => 10
        ]);

        $payload = [
            'product_variant_id' => $variant->id,
            'type' => 'adjustment_in',
            'quantity' => 5,
            'reason' => 'Received supplier sample',
            'notes' => 'Extra sample piece'
        ];

        $response = $this->postJson('/api/admin/inventory/adjust', $payload);

        $response->assertStatus(201)
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.stock_before', 10)
            ->assertJsonPath('data.stock_after', 15)
            ->assertJsonPath('data.quantity', 5)
            ->assertJsonPath('data.direction', 'IN');

        $this->assertDatabaseHas('product_variants', [
            'id' => $variant->id,
            'stock_quantity' => 15
        ]);

        $this->assertDatabaseHas('inventory_ledger', [
            'product_variant_id' => $variant->id,
            'type' => 'ADJUSTMENT',
            'direction' => 'IN',
            'quantity' => 5,
            'stock_before' => 10,
            'stock_after' => 15
        ]);
    }

    public function test_can_adjust_stock_out_and_record_ledger(): void
    {
        $variant = ProductVariant::factory()->create([
            'sku' => 'TEST-SKU-2',
            'stock_quantity' => 10
        ]);

        $payload = [
            'product_variant_id' => $variant->id,
            'type' => 'adjustment_out',
            'quantity' => 4,
            'reason' => 'Internal testing write-off'
        ];

        $response = $this->postJson('/api/admin/inventory/adjust', $payload);

        $response->assertStatus(201)
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.stock_before', 10)
            ->assertJsonPath('data.stock_after', 6)
            ->assertJsonPath('data.quantity', 4)
            ->assertJsonPath('data.direction', 'OUT');

        $this->assertDatabaseHas('product_variants', [
            'id' => $variant->id,
            'stock_quantity' => 6
        ]);
    }

    public function test_cannot_adjust_stock_out_below_zero(): void
    {
        $variant = ProductVariant::factory()->create([
            'sku' => 'TEST-SKU-3',
            'stock_quantity' => 5
        ]);

        $payload = [
            'product_variant_id' => $variant->id,
            'type' => 'adjustment_out',
            'quantity' => 10, // Invalid: exceeds 5
            'reason' => 'Inventory count mismatch'
        ];

        $response = $this->postJson('/api/admin/inventory/adjust', $payload);

        $response->assertStatus(400)
            ->assertJsonPath('success', false)
            ->assertJsonPath('error_code', 'BAD_REQUEST');

        // Variant stock remains unchanged
        $this->assertDatabaseHas('product_variants', [
            'id' => $variant->id,
            'stock_quantity' => 5
        ]);
    }

    public function test_can_list_ledger_history_with_filters(): void
    {
        $variant = ProductVariant::factory()->create(['sku' => 'LEDGER-V1']);

        // Create dummy ledger entries
        InventoryLedger::create([
            'product_variant_id' => $variant->id,
            'type' => 'PURCHASE',
            'direction' => 'IN',
            'quantity' => 100,
            'stock_before' => 0,
            'stock_after' => 100
        ]);

        InventoryLedger::create([
            'product_variant_id' => $variant->id,
            'type' => 'DAMAGE',
            'direction' => 'OUT',
            'quantity' => 5,
            'stock_before' => 100,
            'stock_after' => 95
        ]);

        $response = $this->getJson('/api/admin/inventory/ledger');
        $response->assertStatus(200)
            ->assertJsonCount(2, 'data');

        $response = $this->getJson('/api/admin/inventory/ledger?type=DAMAGE');
        $response->assertStatus(200)
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.type', 'DAMAGE');
    }
}
