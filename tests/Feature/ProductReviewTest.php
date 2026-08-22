<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Permission;
use App\Models\Product;
use App\Models\ProductReview;
use App\Models\ProductVariant;
use App\Models\Role;
use App\Models\Setting;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ProductReviewTest extends TestCase
{
    use RefreshDatabase;

    protected User $customer;
    protected User $admin;
    protected Product $product;
    protected ProductVariant $variant;

    protected function setUp(): void
    {
        parent::setUp();

        // 1. Create default settings for reviews
        Setting::set('login_required', true, 'reviews', 'boolean');
        Setting::set('verified_purchase_required', true, 'reviews', 'boolean');
        Setting::set('delivered_order_required', true, 'reviews', 'boolean');
        Setting::set('one_review_per_product', true, 'reviews', 'boolean');
        Setting::set('admin_approval_required', true, 'reviews', 'boolean');
        Setting::set('customer_editing_allowed', true, 'reviews', 'boolean');
        Setting::set('customer_deletion_allowed', true, 'reviews', 'boolean');
        Setting::set('customer_images_allowed', true, 'reviews', 'boolean');
        Setting::set('max_images_per_review', 4, 'reviews', 'number');

        // 2. Create category, product, and variant
        $category = Category::create([
            'name' => 'Sarees',
            'slug' => 'sarees',
            'is_active' => true,
        ]);

        $this->product = Product::create([
            'category_id' => $category->id,
            'name' => 'Kanjivaram Silk Saree',
            'slug' => 'kanjivaram-silk-saree',
            'mrp' => 12000.00,
            'selling_price' => 9999.00,
            'is_active' => true,
            'reviews_enabled' => true,
        ]);

        $this->variant = ProductVariant::create([
            'product_id' => $this->product->id,
            'sku' => 'KANJI-SILK-01',
            'size' => 'Free Size',
            'color' => 'Red',
            'stock_quantity' => 10,
        ]);

        // 3. Create permissions and roles safely
        $adminRole = Role::firstOrCreate(
            ['name' => 'super_admin'],
            [
                'slug' => 'super_admin',
                'description' => 'Super Administrator',
                'is_system' => true,
            ]
        );

        $perm = Permission::firstOrCreate(
            ['name' => 'manage_products'],
            [
                'slug' => 'manage_products',
                'module' => 'products',
            ]
        );

        if (!$adminRole->permissions()->where('permissions.id', $perm->id)->exists()) {
            $adminRole->permissions()->attach($perm->id);
        }

        $this->customer = User::factory()->create([
            'first_name' => 'Priya',
            'last_name' => 'Sharma',
            'email' => 'priya@example.com',
        ]);

        $this->admin = User::factory()->create([
            'first_name' => 'Admin',
            'last_name' => 'User',
            'email' => 'admin@mayasree.com',
        ]);

        $this->admin->assignRole($adminRole);

        Storage::fake('public');
    }

    /**
     * Test guest users cannot submit reviews.
     */
    public function test_guest_cannot_submit_review(): void
    {
        $response = $this->postJson("/api/products/{$this->product->id}/reviews", [
            'rating' => 5,
            'review' => 'Exquisite saree with rich silk fabric!',
        ]);

        $this->assertTrue(in_array($response->getStatusCode(), [401, 403]));
    }

    /**
     * Test customer without a purchase cannot review when verified purchase is required.
     */
    public function test_non_purchaser_cannot_submit_review(): void
    {
        $response = $this->actingAs($this->customer, 'sanctum')
            ->postJson("/api/products/{$this->product->id}/reviews", [
                'rating' => 5,
                'review' => 'Exquisite saree with rich silk fabric!',
            ]);

        $response->assertStatus(403)
            ->assertJson([
                'success' => false,
                'reason' => 'not_purchased',
            ]);
    }

    /**
     * Test customer with undelivered order cannot review when delivered order is required.
     */
    public function test_undelivered_order_cannot_submit_review(): void
    {
        $order = Order::create([
            'user_id' => $this->customer->id,
            'status' => 'processing',
            'subtotal' => 9999.00,
            'grand_total' => 9999.00,
            'total_items' => 1,
            'shipping_first_name' => 'Priya',
            'shipping_last_name' => 'Sharma',
            'shipping_phone' => '9876543210',
            'shipping_address_line_1' => '123 Main St',
            'shipping_city' => 'Chennai',
            'shipping_state' => 'Tamil Nadu',
            'shipping_postal_code' => '600001',
            'billing_first_name' => 'Priya',
            'billing_last_name' => 'Sharma',
            'billing_phone' => '9876543210',
            'billing_address_line_1' => '123 Main St',
            'billing_city' => 'Chennai',
            'billing_state' => 'Tamil Nadu',
            'billing_postal_code' => '600001',
        ]);

        OrderItem::create([
            'order_id' => $order->id,
            'product_id' => $this->product->id,
            'product_variant_id' => $this->variant->id,
            'sku' => $this->variant->sku,
            'product_name' => $this->product->name,
            'quantity' => 1,
            'unit_price' => 9999.00,
            'unit_mrp' => 12000.00,
            'total_price' => 9999.00,
        ]);

        $response = $this->actingAs($this->customer, 'sanctum')
            ->postJson("/api/products/{$this->product->id}/reviews", [
                'rating' => 5,
                'review' => 'Exquisite saree with rich silk fabric!',
            ]);

        $response->assertStatus(403)
            ->assertJson([
                'success' => false,
                'reason' => 'order_not_delivered',
            ]);
    }

    /**
     * Test eligible customer with delivered order can submit review.
     */
    public function test_delivered_purchaser_can_submit_review(): void
    {
        $order = Order::create([
            'user_id' => $this->customer->id,
            'status' => 'delivered',
            'delivered_at' => now(),
            'subtotal' => 9999.00,
            'grand_total' => 9999.00,
            'total_items' => 1,
            'shipping_first_name' => 'Priya',
            'shipping_last_name' => 'Sharma',
            'shipping_phone' => '9876543210',
            'shipping_address_line_1' => '123 Main St',
            'shipping_city' => 'Chennai',
            'shipping_state' => 'Tamil Nadu',
            'shipping_postal_code' => '600001',
            'billing_first_name' => 'Priya',
            'billing_last_name' => 'Sharma',
            'billing_phone' => '9876543210',
            'billing_address_line_1' => '123 Main St',
            'billing_city' => 'Chennai',
            'billing_state' => 'Tamil Nadu',
            'billing_postal_code' => '600001',
        ]);

        OrderItem::create([
            'order_id' => $order->id,
            'product_id' => $this->product->id,
            'product_variant_id' => $this->variant->id,
            'sku' => $this->variant->sku,
            'product_name' => $this->product->name,
            'quantity' => 1,
            'unit_price' => 9999.00,
            'unit_mrp' => 12000.00,
            'total_price' => 9999.00,
        ]);

        $imageFile = UploadedFile::fake()->createWithContent('saree_photo.jpg', "\xFF\xD8\xFF\xE0\x00\x10JFIF\x00\x01\x01\x01\x00\x60\x00\x60\x00\x00\xFF\xDB\x00\x43\x00\x08\x06\x06\x07\x06\x05\x08\x07\x07\x07\x09\x09\x08\x0A\x0C\x14\x0D\x0C\x0B\x0B\x0C\x19\x12\x13\x0F\x14\x1D\x1A\x1F\x1E\x1D\x1A\x1C\x1C\x20\x24\x2E\x27\x20\x22\x2C\x23\x1C\x1C\x28\x37\x29\x2C\x30\x31\x34\x34\x34\x1F\x27\x39\x3D\x38\x32\x3C\x2E\x33\x34\x32\xFF\xC0\x00\x0B\x08\x00\x01\x00\x01\x01\x01\x11\x00\xFF\xDA\x00\x08\x01\x01\x00\x00\x3F\x00\xBF\x00\xFF\xD9");

        $response = $this->actingAs($this->customer, 'sanctum')
            ->postJson("/api/products/{$this->product->id}/reviews", [
                'rating' => 5,
                'review' => 'Absolutely gorgeous craftsmanship! The zari work is stunning.',
                'images' => [$imageFile],
            ]);

        $response->assertStatus(201)
            ->assertJson([
                'success' => true,
            ]);

        $this->assertDatabaseHas('product_reviews', [
            'product_id' => $this->product->id,
            'user_id' => $this->customer->id,
            'rating' => 5,
            'status' => 'pending',
            'is_verified_purchase' => true,
        ]);
    }

    /**
     * Test duplicate review per customer/product is prevented.
     */
    public function test_duplicate_review_is_prevented(): void
    {
        ProductReview::create([
            'product_id' => $this->product->id,
            'user_id' => $this->customer->id,
            'rating' => 5,
            'review' => 'First review text here.',
            'status' => 'approved',
            'is_verified_purchase' => true,
        ]);

        $response = $this->actingAs($this->customer, 'sanctum')
            ->postJson("/api/products/{$this->product->id}/reviews", [
                'rating' => 4,
                'review' => 'Trying to submit a second review.',
            ]);

        $response->assertStatus(403)
            ->assertJson([
                'success' => false,
                'reason' => 'already_reviewed',
            ]);
    }

    /**
     * Test admin can approve review and update product rating recalculation.
     */
    public function test_admin_can_approve_review_and_recalculate_rating(): void
    {
        $review = ProductReview::create([
            'product_id' => $this->product->id,
            'user_id' => $this->customer->id,
            'rating' => 5,
            'review' => 'Amazing quality product!',
            'status' => 'pending',
            'is_verified_purchase' => true,
        ]);

        $response = $this->actingAs($this->admin, 'sanctum')
            ->patchJson("/api/admin/reviews/{$review->id}/status", [
                'status' => 'approved',
            ]);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
            ]);

        $this->assertDatabaseHas('product_reviews', [
            'id' => $review->id,
            'status' => 'approved',
        ]);

        $this->product->refresh();
        $this->assertEquals(5.00, (float) $this->product->avg_rating);
        $this->assertEquals(1, $this->product->total_reviews);
    }

    /**
     * Test customer cannot edit another user's review.
     */
    public function test_customer_cannot_edit_another_users_review(): void
    {
        $otherUser = User::factory()->create();
        $review = ProductReview::create([
            'product_id' => $this->product->id,
            'user_id' => $otherUser->id,
            'rating' => 4,
            'review' => 'Good product by other user.',
            'status' => 'approved',
            'is_verified_purchase' => true,
        ]);

        $response = $this->actingAs($this->customer, 'sanctum')
            ->putJson("/api/reviews/{$review->id}", [
                'rating' => 1,
                'review' => 'Attempting malicious edit.',
            ]);

        $response->assertStatus(403);
    }

    /**
     * Test customer can delete their own review.
     */
    public function test_customer_can_delete_own_review(): void
    {
        $review = ProductReview::create([
            'product_id' => $this->product->id,
            'user_id' => $this->customer->id,
            'rating' => 5,
            'review' => 'My review to delete.',
            'status' => 'approved',
            'is_verified_purchase' => true,
        ]);

        $this->product->recalculateRating();
        $this->assertEquals(1, $this->product->fresh()->total_reviews);

        $response = $this->actingAs($this->customer, 'sanctum')
            ->deleteJson("/api/reviews/{$review->id}");

        $response->assertStatus(200);
        $this->assertSoftDeleted('product_reviews', ['id' => $review->id]);
        $this->assertEquals(0, $this->product->fresh()->total_reviews);
    }
}
