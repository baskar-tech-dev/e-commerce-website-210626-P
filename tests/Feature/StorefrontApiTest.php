<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\Category;
use App\Models\Coupon;
use App\Models\Address;
use App\Models\Order;
use App\Models\InventoryLedger;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StorefrontApiTest extends TestCase
{
    use RefreshDatabase;

    protected $customer;
    protected $category;
    protected $product;
    protected $variant;
    protected $coupon;

    protected function setUp(): void
    {
        parent::setUp();

        $this->customer = User::factory()->create([
            'email' => 'buyer@test.com',
            'first_name' => 'John',
            'last_name' => 'Buyer',
        ]);

        $this->category = Category::create([
            'name' => 'Activewear',
            'slug' => 'activewear',
            'is_active' => true,
        ]);

        $this->product = Product::create([
            'category_id' => $this->category->id,
            'name' => 'Pro Joggers',
            'slug' => 'pro-joggers',
            'mrp' => 1999.00,
            'selling_price' => 1499.00,
            'is_active' => true,
        ]);

        $this->variant = ProductVariant::create([
            'product_id' => $this->product->id,
            'sku' => 'PRO-JOG-M',
            'size' => 'M',
            'stock_quantity' => 10,
            'is_active' => true,
        ]);

        $this->coupon = Coupon::create([
            'code' => 'SAVE100',
            'name' => 'Save One Hundred',
            'type' => 'fixed',
            'value' => 100.00,
            'min_order_value' => 500.00,
            'starts_at' => now(),
            'is_active' => true,
        ]);
    }

    public function test_can_query_public_catalog(): void
    {
        $response = $this->getJson('/api/storefront/products');
        
        $response->assertStatus(200)
            ->assertJsonPath('success', true)
            ->assertJsonCount(1, 'data');

        $responseDetail = $this->getJson("/api/storefront/products/{$this->product->id}");
        $responseDetail->assertStatus(200)
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.name', 'Pro Joggers');
    }

    public function test_sold_out_products_appear_on_last_page_in_catalog(): void
    {
        // 1. Create a newer product that is SOLD OUT (stock = 0)
        $soldOutProduct = Product::create([
            'category_id' => $this->category->id,
            'name' => 'Sold Out Saree',
            'slug' => 'sold-out-saree',
            'mrp' => 2999.00,
            'selling_price' => 2499.00,
            'is_active' => true,
        ]);
        ProductVariant::create([
            'product_id' => $soldOutProduct->id,
            'sku' => 'SO-SAR-1',
            'stock_quantity' => 0,
            'is_active' => true,
        ]);

        // 2. Create another in-stock product
        $inStockProduct2 = Product::create([
            'category_id' => $this->category->id,
            'name' => 'In Stock Blouse',
            'slug' => 'in-stock-blouse',
            'mrp' => 999.00,
            'selling_price' => 799.00,
            'is_active' => true,
        ]);
        ProductVariant::create([
            'product_id' => $inStockProduct2->id,
            'sku' => 'IS-BL-1',
            'stock_quantity' => 5,
            'is_active' => true,
        ]);

        // Query Page 1 with per_page = 2 (Total products = 3: $this->product, $inStockProduct2, $soldOutProduct)
        $responsePage1 = $this->getJson('/api/storefront/products?per_page=2&page=1');
        $responsePage1->assertStatus(200)
            ->assertJsonPath('meta.current_page', 1)
            ->assertJsonPath('meta.last_page', 2)
            ->assertJsonCount(2, 'data');

        $page1ProductNames = collect($responsePage1->json('data'))->pluck('name')->toArray();
        // Page 1 should contain only in-stock items
        $this->assertContains('In Stock Blouse', $page1ProductNames);
        $this->assertContains('Pro Joggers', $page1ProductNames);
        $this->assertNotContains('Sold Out Saree', $page1ProductNames);

        // Query Page 2 (Last Page)
        $responsePage2 = $this->getJson('/api/storefront/products?per_page=2&page=2');
        $responsePage2->assertStatus(200)
            ->assertJsonPath('meta.current_page', 2)
            ->assertJsonPath('meta.last_page', 2)
            ->assertJsonCount(1, 'data');

        $page2ProductNames = collect($responsePage2->json('data'))->pluck('name')->toArray();
        // Page 2 should contain the sold out item
        $this->assertContains('Sold Out Saree', $page2ProductNames);

        // Even with sort_by=price_low_high (where sold-out item selling_price is between or higher/lower)
        // In-stock items should still come first!
        $responseSortPrice = $this->getJson('/api/storefront/products?per_page=2&page=1&sort_by=price_low_high');
        $responseSortPrice->assertStatus(200);
        $sortPriceNames = collect($responseSortPrice->json('data'))->pluck('name')->toArray();
        $this->assertEquals(['In Stock Blouse', 'Pro Joggers'], $sortPriceNames);
    }

    public function test_can_checkout_placed_order_successfully(): void
    {
        $this->actingAs($this->customer);

        $response = $this->postJson('/api/storefront/checkout', [
            'shipping_first_name' => 'John',
            'shipping_last_name' => 'Buyer',
            'shipping_phone' => '9876543210',
            'shipping_address_line_1' => 'Flat 202, Wing B',
            'shipping_city' => 'Mumbai',
            'shipping_state' => 'Maharashtra',
            'shipping_postal_code' => '400001',
            'payment_method' => 'COD',
            'coupon_code' => 'SAVE100',
            'items' => [
                [
                    'product_variant_id' => $this->variant->id,
                    'quantity' => 2,
                ]
            ]
        ]);

        $response->assertStatus(200)
            ->assertJsonPath('success', true)
            ->assertJsonStructure([
                'success',
                'message',
                'data' => ['order_number', 'grand_total']
            ]);

        // Assert Order totals in DB
        // Subtotal = 1499 * 2 = 2998. Discount = 100. Shipping = 0 (subtotal >= 999). Grand total = 2898.
        $this->assertDatabaseHas('orders', [
            'user_id' => $this->customer->id,
            'subtotal' => 2998.00,
            'discount_amount' => 100.00,
            'grand_total' => 2898.00,
            'total_items' => 2,
        ]);

        // Assert Variant Stock decremented: 10 - 2 = 8
        $this->assertEquals(8, $this->variant->fresh()->stock_quantity);

        // Assert Stock Ledger OUT recorded
        $this->assertDatabaseHas('inventory_ledger', [
            'product_variant_id' => $this->variant->id,
            'type' => 'SALE',
            'direction' => 'OUT',
            'quantity' => 2,
            'reference_type' => 'Order',
        ]);
    }

    public function test_can_checkout_with_online_payment_reserves_stock(): void
    {
        $this->actingAs($this->customer);

        $response = $this->postJson('/api/storefront/checkout', [
            'shipping_first_name' => 'John',
            'shipping_last_name' => 'Buyer',
            'shipping_phone' => '9876543210',
            'shipping_address_line_1' => 'Flat 202, Wing B',
            'shipping_city' => 'Mumbai',
            'shipping_state' => 'Maharashtra',
            'shipping_postal_code' => '400001',
            'payment_method' => 'upi',
            'items' => [
                [
                    'product_variant_id' => $this->variant->id,
                    'quantity' => 2,
                ]
            ]
        ]);

        $response->assertStatus(200)
            ->assertJsonPath('success', true);

        // Assert Order totals in DB
        $this->assertDatabaseHas('orders', [
            'user_id' => $this->customer->id,
            'payment_method' => 'upi',
            'payment_status' => 'pending',
            'status' => \App\Models\Order::STATUS_ORDER_PLACED,
        ]);

        // Assert Stock is reserved: stock_quantity is decremented to 8, and reserved_quantity = 2
        $this->variant->refresh();
        $this->assertEquals(8, $this->variant->stock_quantity);
        $this->assertEquals(2, $this->variant->reserved_quantity);
    }

    public function test_rejects_checkout_if_insufficient_stock(): void
    {
        $this->actingAs($this->customer);

        $response = $this->postJson('/api/storefront/checkout', [
            'shipping_first_name' => 'John',
            'shipping_last_name' => 'Buyer',
            'shipping_phone' => '9876543210',
            'shipping_address_line_1' => 'Flat 202, Wing B',
            'shipping_city' => 'Mumbai',
            'shipping_state' => 'Maharashtra',
            'shipping_postal_code' => '400001',
            'payment_method' => 'COD',
            'items' => [
                [
                    'product_variant_id' => $this->variant->id,
                    'quantity' => 15, // Only 10 available
                ]
            ]
        ]);

        $response->assertStatus(422);
    }

    public function test_can_manage_customer_address_book(): void
    {
        $this->actingAs($this->customer);

        // 1. Create Address
        $response = $this->postJson('/api/customer/addresses', [
            'label' => 'Home',
            'first_name' => 'John',
            'last_name' => 'Buyer',
            'phone' => '9876543210',
            'address_line_1' => '45, Link Road',
            'city' => 'Mumbai',
            'state' => 'Maharashtra',
            'postal_code' => '400001',
            'is_default_shipping' => true,
        ]);

        $response->assertStatus(200)
            ->assertJsonPath('success', true);
        $this->assertDatabaseHas('addresses', ['label' => 'Home', 'user_id' => $this->customer->id]);

        $address = Address::where('label', 'Home')->first();

        // 2. Fetch Profile Details
        $profileResponse = $this->getJson('/api/customer/profile');
        $profileResponse->assertStatus(200)
            ->assertJsonPath('success', true)
            ->assertJsonCount(1, 'data.addresses');

        // 3. Delete Address
        $deleteResponse = $this->deleteJson("/api/customer/addresses/{$address->id}");
        $deleteResponse->assertStatus(200);
        $this->assertSoftDeleted('addresses', ['id' => $address->id]);
    }
}
