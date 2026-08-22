<?php

namespace Tests\Feature;

use App\Models\Courier;
use App\Models\Order;
use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CourierApiTest extends TestCase
{
    use RefreshDatabase;

    protected $admin;
    protected $customer;

    protected function setUp(): void
    {
        parent::setUp();

        $perm = Permission::firstOrCreate(['name' => 'manage_orders'], ['description' => 'Manage Orders']);
        $role = Role::firstOrCreate(['name' => 'super_admin'], ['description' => 'Super Admin']);
        if (!$role->permissions()->where('permissions.id', $perm->id)->exists()) {
            $role->permissions()->attach($perm);
        }

        $this->admin = User::factory()->create();
        $this->admin->assignRole($role);

        $this->customer = User::factory()->create([
            'email' => 'customer@test.com',
            'first_name' => 'Customer',
            'last_name' => 'User',
        ]);
    }

    public function test_can_list_and_filter_couriers(): void
    {
        Courier::create([
            'name' => 'Delhivery Express',
            'code' => 'delhivery',
            'tracking_page_link' => 'https://www.delhivery.com/track/package/{tracking_number}',
            'contact_person' => 'Delhivery Admin Lead',
            'contact_number' => '+91 80698 56100',
            'is_active' => true,
        ]);

        Courier::create([
            'name' => 'Blue Dart Air',
            'code' => 'bluedart',
            'tracking_page_link' => 'https://www.bluedart.com/tracking?track={tracking_number}',
            'contact_person' => 'Blue Dart Admin Lead',
            'contact_number' => '+91 1860 233 1234',
            'is_active' => false,
        ]);

        $response = $this->actingAs($this->admin)->getJson('/api/admin/couriers');
        $response->assertStatus(200)
            ->assertJsonPath('success', true)
            ->assertJsonCount(2, 'data');

        // Active only list
        $activeResponse = $this->actingAs($this->admin)->getJson('/api/admin/couriers/active');
        $activeResponse->assertStatus(200)
            ->assertJsonPath('success', true)
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.name', 'Delhivery Express');
    }

    public function test_can_create_courier(): void
    {
        $payload = [
            'name' => 'DTDC Express',
            'code' => 'dtdc',
            'tracking_page_link' => 'https://www.dtdc.in/tracking/shipment-tracking.asp?ref={tracking_number}',
            'contact_person' => 'DTDC Dispatcher',
            'contact_number' => '+91 73057 73057',
            'contact_email' => 'support@dtdc.com',
            'is_active' => true,
            'sort_order' => 1,
            'notes' => 'Pan-India standard logistics',
        ];

        $response = $this->actingAs($this->admin)->postJson('/api/admin/couriers', $payload);
        $response->assertStatus(201)
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.name', 'DTDC Express');

        $this->assertDatabaseHas('couriers', [
            'name' => 'DTDC Express',
            'code' => 'dtdc',
        ]);
    }

    public function test_can_update_and_toggle_courier(): void
    {
        $courier = Courier::create([
            'name' => 'ST Courier',
            'code' => 'st_courier',
            'tracking_page_link' => 'https://stcourier.com/track/details/{tracking_number}',
            'contact_person' => 'Hub Admin',
            'contact_number' => '+91 98424 00000',
            'is_active' => true,
        ]);

        $response = $this->actingAs($this->admin)->putJson("/api/admin/couriers/{$courier->id}", [
            'name' => 'ST Courier Express',
            'code' => 'st_courier',
            'tracking_page_link' => 'https://stcourier.com/track/details/{tracking_number}',
            'contact_person' => 'Senior Hub Admin',
            'contact_number' => '+91 98424 11111',
            'is_active' => true,
        ]);

        $response->assertStatus(200)
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.contact_person', 'Senior Hub Admin');

        // Toggle active
        $toggleResponse = $this->actingAs($this->admin)->patchJson("/api/admin/couriers/{$courier->id}/toggle");
        $toggleResponse->assertStatus(200)
            ->assertJsonPath('data.is_active', false);
    }

    public function test_can_assign_courier_and_auto_generate_tracking_url(): void
    {
        $courier = Courier::create([
            'name' => 'Delhivery',
            'code' => 'delhivery',
            'tracking_page_link' => 'https://www.delhivery.com/track/package/{tracking_number}',
            'contact_person' => 'Ramesh Hub Manager',
            'contact_number' => '+91 98765 43210',
            'is_active' => true,
        ]);

        $order = Order::create([
            'user_id' => $this->customer->id,
            'status' => 'processing',
            'payment_status' => 'paid',
            'subtotal' => 1200.00,
            'grand_total' => 1200.00,
            'total_items' => 1,
            'shipping_first_name' => 'Customer',
            'shipping_last_name' => 'User',
            'shipping_phone' => '9876543210',
            'shipping_address_line_1' => '123 Cross St',
            'shipping_city' => 'Chennai',
            'shipping_state' => 'Tamil Nadu',
            'shipping_postal_code' => '600001',
            'billing_first_name' => 'Customer',
            'billing_last_name' => 'User',
            'billing_phone' => '9876543210',
            'billing_address_line_1' => '123 Cross St',
            'billing_city' => 'Chennai',
            'billing_state' => 'Tamil Nadu',
            'billing_postal_code' => '600001',
        ]);

        $response = $this->actingAs($this->admin)->putJson("/api/admin/orders/{$order->id}/shipping", [
            'courier_id' => $courier->id,
            'courier_name' => 'Delhivery',
            'tracking_number' => 'DEL99887766',
        ]);

        $response->assertStatus(200)
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.courier_name', 'Delhivery')
            ->assertJsonPath('data.tracking_number', 'DEL99887766')
            ->assertJsonPath('data.courier_tracking_url', 'https://www.delhivery.com/track/package/DEL99887766')
            ->assertJsonPath('data.courier_person_name', 'Ramesh Hub Manager')
            ->assertJsonPath('data.courier_contact_number', '+91 98765 43210');

        // Verify Customer Profile returns the tracking URL correctly
        $profileResponse = $this->actingAs($this->customer)->getJson('/api/customer/profile');
        $profileResponse->assertStatus(200)
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.orders.0.courier_name', 'Delhivery')
            ->assertJsonPath('data.orders.0.tracking_number', 'DEL99887766')
            ->assertJsonPath('data.orders.0.courier_tracking_url', 'https://www.delhivery.com/track/package/DEL99887766');
    }
}
