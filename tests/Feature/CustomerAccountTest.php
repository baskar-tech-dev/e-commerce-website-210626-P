<?php

namespace Tests\Feature;

use App\Models\Address;
use App\Models\Order;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class CustomerAccountTest extends TestCase
{
    use RefreshDatabase;

    public function test_authenticated_customer_can_view_own_profile(): void
    {
        $user = User::create([
            'first_name' => 'Kavitha',
            'last_name' => 'Raman',
            'name' => 'Kavitha Raman',
            'email' => 'kavitha@example.com',
            'password' => Hash::make('password'),
        ]);

        $response = $this->actingAs($user, 'sanctum')
            ->getJson('/api/customer/profile');

        $response->assertStatus(200)
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.email', 'kavitha@example.com')
            ->assertJsonPath('data.first_name', 'Kavitha');
    }

    public function test_unauthenticated_request_is_rejected(): void
    {
        $this->app['auth']->forgetGuards();

        $response = $this->getJson('/api/customer/profile');
        $response->assertStatus(401);
    }

    public function test_authenticated_customer_can_update_profile(): void
    {
        $user = User::create([
            'first_name' => 'Deepa',
            'last_name' => 'S',
            'name' => 'Deepa S',
            'email' => 'deepa@example.com',
            'phone' => '1111111111',
            'password' => Hash::make('old_password'),
        ]);

        $response = $this->actingAs($user, 'sanctum')
            ->putJson('/api/customer/profile', [
                'first_name' => 'Deepalakshmi',
                'last_name' => 'Sundaram',
                'email' => 'deepalakshmi@example.com',
                'phone' => '9944285102',
            ]);

        $response->assertStatus(200)
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.first_name', 'Deepalakshmi')
            ->assertJsonPath('data.email', 'deepalakshmi@example.com');

        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'email' => 'deepalakshmi@example.com',
            'first_name' => 'Deepalakshmi',
        ]);
    }

    public function test_customer_can_view_only_own_orders(): void
    {
        $userA = User::create(['name' => 'User A', 'email' => 'usera@example.com', 'password' => bcrypt('password')]);
        $userB = User::create(['name' => 'User B', 'email' => 'userb@example.com', 'password' => bcrypt('password')]);

        $orderA = Order::create([
            'order_number' => 'VIBE-A1',
            'user_id' => $userA->id,
            'status' => 'delivered',
            'payment_status' => 'paid',
            'subtotal' => 1500,
            'grand_total' => 1500,
            'total_items' => 1,
            'shipping_first_name' => 'User',
            'shipping_last_name' => 'A',
            'shipping_phone' => '9999999999',
            'shipping_address_line_1' => 'Street 1',
            'shipping_city' => 'Tirupur',
            'shipping_state' => 'Tamil Nadu',
            'shipping_postal_code' => '641601',
            'shipping_country' => 'IN',
            'billing_first_name' => 'User',
            'billing_last_name' => 'A',
            'billing_phone' => '9999999999',
            'billing_address_line_1' => 'Street 1',
            'billing_city' => 'Tirupur',
            'billing_state' => 'Tamil Nadu',
            'billing_postal_code' => '641601',
            'billing_country' => 'IN',
        ]);

        $orderB = Order::create([
            'order_number' => 'VIBE-B1',
            'user_id' => $userB->id,
            'status' => 'delivered',
            'payment_status' => 'paid',
            'subtotal' => 2500,
            'grand_total' => 2500,
            'total_items' => 1,
            'shipping_first_name' => 'User',
            'shipping_last_name' => 'B',
            'shipping_phone' => '8888888888',
            'shipping_address_line_1' => 'Street 2',
            'shipping_city' => 'Tirupur',
            'shipping_state' => 'Tamil Nadu',
            'shipping_postal_code' => '641601',
            'shipping_country' => 'IN',
            'billing_first_name' => 'User',
            'billing_last_name' => 'B',
            'billing_phone' => '8888888888',
            'billing_address_line_1' => 'Street 2',
            'billing_city' => 'Tirupur',
            'billing_state' => 'Tamil Nadu',
            'billing_postal_code' => '641601',
            'billing_country' => 'IN',
        ]);

        // User A requests own orders list
        $response = $this->actingAs($userA, 'sanctum')
            ->getJson('/api/customer/orders');

        $response->assertStatus(200)
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.order_number', 'VIBE-A1');

        // User A attempts to view User B's order detail directly
        $responseDetail = $this->actingAs($userA, 'sanctum')
            ->getJson('/api/customer/orders/' . $orderB->id);

        $responseDetail->assertStatus(404);
    }
}
