<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Product;
use App\Models\User;
use App\Models\Wishlist;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CartWishlistMergeTest extends TestCase
{
    use RefreshDatabase;

    public function test_customer_can_add_get_delete_wishlist_items(): void
    {
        $category = Category::create(['name' => 'Sarees', 'slug' => 'sarees']);
        $product = Product::create([
            'category_id' => $category->id,
            'name' => 'Silk Saree Blue',
            'slug' => 'silk-saree-blue',
            'mrp' => 2999,
            'selling_price' => 1999,
            'stock_quantity' => 10,
        ]);

        $user = User::create([
            'name' => 'Wishlist Customer',
            'email' => 'wishlist@example.com',
            'password' => bcrypt('password'),
        ]);

        // 1. Add to wishlist
        $resStore = $this->actingAs($user, 'sanctum')
            ->postJson('/api/customer/wishlist', [
                'product_id' => $product->id,
            ]);

        $resStore->assertStatus(200)
            ->assertJsonPath('success', true);

        $this->assertDatabaseHas('wishlists', [
            'user_id' => $user->id,
            'product_id' => $product->id,
        ]);

        // 2. Fetch wishlist
        $resIndex = $this->actingAs($user, 'sanctum')
            ->getJson('/api/customer/wishlist');

        $resIndex->assertStatus(200)
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.name', 'Silk Saree Blue');

        // 3. Remove from wishlist
        $resDestroy = $this->actingAs($user, 'sanctum')
            ->deleteJson('/api/customer/wishlist/' . $product->id);

        $resDestroy->assertStatus(200)
            ->assertJsonPath('success', true);

        $this->assertDatabaseMissing('wishlists', [
            'user_id' => $user->id,
            'product_id' => $product->id,
        ]);
    }

    public function test_customer_can_merge_guest_wishlist_items(): void
    {
        $category = Category::create(['name' => 'Blouses', 'slug' => 'blouses']);
        $product1 = Product::create(['category_id' => $category->id, 'name' => 'Blouse 1', 'slug' => 'b1', 'mrp' => 1000, 'selling_price' => 800, 'stock_quantity' => 5]);
        $product2 = Product::create(['category_id' => $category->id, 'name' => 'Blouse 2', 'slug' => 'b2', 'mrp' => 1200, 'selling_price' => 900, 'stock_quantity' => 5]);

        $user = User::create([
            'name' => 'Merge Customer',
            'email' => 'merge@example.com',
            'password' => bcrypt('password'),
        ]);

        // Pre-existing DB item
        Wishlist::create(['user_id' => $user->id, 'product_id' => $product1->id]);

        // Guest attempts to merge product1 and product2
        $response = $this->actingAs($user, 'sanctum')
            ->postJson('/api/customer/wishlist/merge', [
                'items' => [
                    ['id' => $product1->id],
                    ['id' => $product2->id],
                ]
            ]);

        $response->assertStatus(200)
            ->assertJsonCount(2, 'data');

        $this->assertDatabaseHas('wishlists', ['user_id' => $user->id, 'product_id' => $product1->id]);
        $this->assertDatabaseHas('wishlists', ['user_id' => $user->id, 'product_id' => $product2->id]);
    }
}
