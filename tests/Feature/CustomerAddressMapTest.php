<?php

namespace Tests\Feature;

use App\Models\Address;
use App\Models\Category;
use App\Models\Order;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class CustomerAddressMapTest extends TestCase
{
    use RefreshDatabase;

    protected User $customer;
    protected Role $customerRole;

    protected function setUp(): void
    {
        parent::setUp();

        $this->customerRole = Role::firstOrCreate(['name' => 'customer'], [
            'display_name' => 'Customer',
            'is_system' => true,
        ]);

        $this->customer = User::create([
            'uuid' => (string) Str::uuid(),
            'first_name' => 'Kavitha',
            'last_name' => 'Ramasamy',
            'name' => 'Kavitha Ramasamy',
            'email' => 'kavitha@example.com',
            'phone' => '9876543210',
            'password' => bcrypt('Password@123'),
            'role_id' => $this->customerRole->id,
            'is_active' => true,
        ]);
    }

    public function test_customer_can_add_multiple_addresses_with_map_urls(): void
    {
        // 1. Add Home address with Google Map pin link
        $homeRes = $this->actingAs($this->customer, 'sanctum')->postJson('/api/customer/addresses', [
            'label' => 'Home',
            'first_name' => 'Kavitha',
            'last_name' => 'Ramasamy',
            'phone' => '9876543210',
            'address_line_1' => 'Flat 402, Royal Residency',
            'address_line_2' => 'Solipalayam Main Road',
            'landmark' => 'Opposite Solipalayam Temple',
            'city' => 'Tiruppur',
            'state' => 'Tamil Nadu',
            'postal_code' => '641604',
            'map_url' => 'https://maps.google.com/?q=11.1219,77.3400',
            'latitude' => 11.1219,
            'longitude' => 77.3400,
            'is_default_shipping' => true,
        ]);

        $homeRes->assertStatus(200);
        $homeRes->assertJsonPath('success', true);
        $homeRes->assertJsonPath('data.label', 'Home');
        $homeRes->assertJsonPath('data.landmark', 'Opposite Solipalayam Temple');
        $homeRes->assertJsonPath('data.map_url', 'https://maps.google.com/?q=11.1219,77.3400');
        $homeRes->assertJsonPath('data.is_default_shipping', true);

        // 2. Add Office address
        $officeRes = $this->actingAs($this->customer, 'sanctum')->postJson('/api/customer/addresses', [
            'label' => 'Office',
            'first_name' => 'Kavitha',
            'last_name' => 'Ramasamy',
            'phone' => '9876543210',
            'address_line_1' => 'Tech Park, Floor 3',
            'address_line_2' => 'Avinashi Road',
            'landmark' => 'Near Bus Terminal',
            'city' => 'Coimbatore',
            'state' => 'Tamil Nadu',
            'postal_code' => '641014',
            'map_url' => 'https://maps.app.goo.gl/example123',
            'is_default_shipping' => false,
        ]);

        $officeRes->assertStatus(200);
        $officeRes->assertJsonPath('success', true);
        $officeRes->assertJsonPath('data.label', 'Office');

        // Customer now has 2 distinct addresses
        $this->assertEquals(2, Address::where('user_id', $this->customer->id)->count());

        // Check getProfile returns both addresses with map data
        $profileRes = $this->actingAs($this->customer, 'sanctum')->getJson('/api/customer/profile');
        $profileRes->assertStatus(200);
        $addresses = $profileRes->json('data.addresses');
        $this->assertCount(2, $addresses);
    }

    public function test_setting_new_default_address_unsets_previous_default(): void
    {
        $addr1 = Address::create([
            'user_id' => $this->customer->id,
            'label' => 'Home',
            'first_name' => 'Kavitha',
            'last_name' => 'Ramasamy',
            'phone' => '9876543210',
            'address_line_1' => 'Old House',
            'city' => 'Tiruppur',
            'state' => 'Tamil Nadu',
            'postal_code' => '641604',
            'is_default_shipping' => true,
        ]);

        $addr2 = Address::create([
            'user_id' => $this->customer->id,
            'label' => 'Office',
            'first_name' => 'Kavitha',
            'last_name' => 'Ramasamy',
            'phone' => '9876543210',
            'address_line_1' => 'New Office',
            'city' => 'Coimbatore',
            'state' => 'Tamil Nadu',
            'postal_code' => '641014',
            'is_default_shipping' => false,
        ]);

        // Update addr2 to be default
        $updateRes = $this->actingAs($this->customer, 'sanctum')->postJson('/api/customer/addresses', [
            'id' => $addr2->id,
            'label' => 'Office',
            'first_name' => 'Kavitha',
            'last_name' => 'Ramasamy',
            'phone' => '9876543210',
            'address_line_1' => 'New Office',
            'city' => 'Coimbatore',
            'state' => 'Tamil Nadu',
            'postal_code' => '641014',
            'is_default_shipping' => true,
        ]);

        $updateRes->assertStatus(200);

        // Verify addr1 is no longer default, addr2 is default
        $this->assertFalse((bool) $addr1->fresh()->is_default_shipping);
        $this->assertTrue((bool) $addr2->fresh()->is_default_shipping);
    }

    public function test_checkout_records_shipping_map_url_and_persists_order(): void
    {
        $category = Category::create([
            'name' => 'Silk Sarees',
            'slug' => 'silk-sarees-' . uniqid(),
            'is_active' => true,
        ]);

        $product = Product::create([
            'uuid' => (string) Str::uuid(),
            'name' => 'Kanchipuram Pure Silk Saree',
            'slug' => 'kanchipuram-pure-silk-saree-' . uniqid(),
            'category_id' => $category->id,
            'selling_price' => 3500.00,
            'mrp' => 5000.00,
            'status' => 'active',
            'is_active' => true,
        ]);

        $variant = ProductVariant::create([
            'product_id' => $product->id,
            'sku' => 'KAN-SILK-001-' . uniqid(),
            'price' => 3500.00,
            'stock_quantity' => 10,
            'is_active' => true,
        ]);

        $mapUrl = 'https://maps.google.com/?q=11.1219,77.3400';

        $res = $this->actingAs($this->customer, 'sanctum')->postJson('/api/storefront/checkout', [
            'shipping_first_name' => 'Kavitha',
            'shipping_last_name' => 'Ramasamy',
            'shipping_phone' => '9876543210',
            'shipping_email' => 'kavitha@example.com',
            'shipping_address_line_1' => '42 Heritage Colony',
            'shipping_address_line_2' => 'Opposite Silk Center',
            'shipping_city' => 'Tiruppur',
            'shipping_state' => 'Tamil Nadu',
            'shipping_postal_code' => '641604',
            'shipping_map_url' => $mapUrl,
            'payment_method' => 'online',
            'save_to_address_book' => true,
            'address_label' => 'Home',
            'items' => [
                [
                    'product_variant_id' => $variant->id,
                    'quantity' => 1,
                ]
            ],
        ]);

        $res->assertStatus(200);
        $res->assertJsonPath('success', true);

        $orderId = $res->json('data.order_id');
        $order = Order::find($orderId);
        $this->assertNotNull($order);
        $this->assertEquals($mapUrl, $order->shipping_map_url);

        // Address was auto-saved to address book with map_url
        $savedAddress = Address::where('user_id', $this->customer->id)
            ->where('postal_code', '641604')
            ->first();
        $this->assertNotNull($savedAddress);
        $this->assertEquals($mapUrl, $savedAddress->map_url);
    }
}
