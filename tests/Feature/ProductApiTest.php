<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\Tag;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProductApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_list_products_with_filters(): void
    {
        $category = Category::factory()->create();
        $product1 = Product::factory()->create([
            'category_id' => $category->id,
            'name' => 'Blue Denim Jacket',
            'is_active' => true,
            'selling_price' => 1500.00
        ]);

        $product2 = Product::factory()->create([
            'category_id' => Category::factory()->create()->id,
            'name' => 'Red Dress',
            'is_active' => false,
            'selling_price' => 2500.00
        ]);

        // 1. List all
        $response = $this->getJson('/api/admin/products');
        $response->assertStatus(200)
            ->assertJsonPath('success', true)
            ->assertJsonCount(2, 'data');

        // 2. Filter by category
        $response = $this->getJson("/api/admin/products?category_id={$category->id}");
        $response->assertStatus(200)->assertJsonCount(1, 'data');

        // 3. Filter by active
        $response = $this->getJson('/api/admin/products?is_active=true');
        $response->assertStatus(200)->assertJsonCount(1, 'data');

        // 4. Filter by search text
        $response = $this->getJson('/api/admin/products?search=Jacket');
        $response->assertStatus(200)->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.name', 'Blue Denim Jacket');
    }

    public function test_can_create_product_with_variants_images_and_tags(): void
    {
        $category = Category::factory()->create();
        $tag = Tag::factory()->create();
        $payload = [
            'category_id' => $category->id,
            'name' => 'Fancy Shirt',
            'mrp' => 1000.00,
            'selling_price' => 800.00,
            'tag_ids' => [$tag->id],
            'variants' => [
                [
                    'sku' => 'SHIRT-S',
                    'size' => 'S',
                    'color' => 'White',
                    'stock_quantity' => 20
                ],
                [
                    'sku' => 'SHIRT-M',
                    'size' => 'M',
                    'color' => 'White',
                    'stock_quantity' => 15
                ]
            ],
            'images' => [
                [
                    'url' => 'http://example.com/shirt1.jpg',
                    'is_primary' => true,
                    'variant_sku' => 'SHIRT-S'
                ]
            ]
        ];

        $response = $this->postJson('/api/admin/products', $payload);

        $response->assertStatus(201)
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.name', 'Fancy Shirt')
            ->assertJsonCount(2, 'data.variants')
            ->assertJsonCount(1, 'data.images')
            ->assertJsonCount(1, 'data.tags');

        $this->assertDatabaseHas('products', ['name' => 'Fancy Shirt', 'slug' => 'fancy-shirt']);
        $this->assertDatabaseHas('product_variants', ['sku' => 'SHIRT-S', 'stock_quantity' => 20]);
        $this->assertDatabaseHas('product_variants', ['sku' => 'SHIRT-M', 'stock_quantity' => 15]);
        $this->assertDatabaseHas('product_images', ['url' => 'http://example.com/shirt1.jpg', 'is_primary' => true]);

        // Verify variant_id was set on the image correctly since we mapped variant_sku
        $variant = ProductVariant::where('sku', 'SHIRT-S')->first();
        $this->assertDatabaseHas('product_images', [
            'url' => 'http://example.com/shirt1.jpg',
            'variant_id' => $variant->id
        ]);
    }

    public function test_can_update_product_and_sync_relations(): void
    {
        $product = Product::factory()->create(['name' => 'Old Hat', 'slug' => 'old-hat']);
        $variant1 = ProductVariant::factory()->create(['product_id' => $product->id, 'sku' => 'HAT-S']);
        $variant2 = ProductVariant::factory()->create(['product_id' => $product->id, 'sku' => 'HAT-M']);

        $payload = [
            'name' => 'New Hat',
            'mrp' => 1200.00,
            'selling_price' => 1000.00,
            'variants' => [
                // Update variant1
                [
                    'id' => $variant1->id,
                    'sku' => 'HAT-S',
                    'size' => 'S',
                    'stock_quantity' => 50
                ],
                // Add new variant, variant2 is missing so it will be deleted
                [
                    'sku' => 'HAT-L',
                    'size' => 'L',
                    'stock_quantity' => 10
                ]
            ]
        ];

        $response = $this->putJson("/api/admin/products/{$product->id}", $payload);

        $response->assertStatus(200)
            ->assertJsonPath('data.name', 'New Hat')
            ->assertJsonCount(2, 'data.variants');

        $this->assertDatabaseHas('products', ['id' => $product->id, 'name' => 'New Hat']);
        $this->assertDatabaseHas('product_variants', ['id' => $variant1->id, 'stock_quantity' => 50]);
        $this->assertDatabaseHas('product_variants', ['sku' => 'HAT-L', 'stock_quantity' => 10]);
        
        // Assert variant2 was deleted
        $this->assertSoftDeleted('product_variants', ['id' => $variant2->id]);
    }

    public function test_can_delete_product_with_cascade(): void
    {
        $product = Product::factory()->create();
        $variant = ProductVariant::factory()->create(['product_id' => $product->id]);

        $response = $this->deleteJson("/api/admin/products/{$product->id}");

        $response->assertStatus(200)
            ->assertJsonPath('success', true);

        $this->assertSoftDeleted('products', ['id' => $product->id]);
        $this->assertSoftDeleted('product_variants', ['id' => $variant->id]);
    }

    public function test_enforces_variant_sku_uniqueness(): void
    {
        ProductVariant::factory()->create(['sku' => 'TAKEN-SKU']);

        $category = Category::factory()->create();
        $payload = [
            'category_id' => $category->id,
            'name' => 'New Product',
            'mrp' => 1000.00,
            'selling_price' => 800.00,
            'variants' => [
                [
                    'sku' => 'TAKEN-SKU',
                    'stock_quantity' => 10
                ]
            ]
        ];

        $response = $this->postJson('/api/admin/products', $payload);

        $response->assertStatus(422)
            ->assertJsonValidationErrors('variants');
    }

    public function test_enforces_price_selling_price_lte_mrp_check(): void
    {
        $category = Category::factory()->create();
        $payload = [
            'category_id' => $category->id,
            'name' => 'New Product',
            'mrp' => 500.00,
            'selling_price' => 600.00, // Invalid: selling_price > mrp
        ];

        $response = $this->postJson('/api/admin/products', $payload);

        $response->assertStatus(422)
            ->assertJsonValidationErrors('selling_price');
    }
}
