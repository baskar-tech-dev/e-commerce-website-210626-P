<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\CustomerProfile;
use App\Models\Address;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CustomerApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_list_customers_with_filters_and_sorting(): void
    {
        // Customer 1: spent 500, orders 2, active
        $user1 = User::create([
            'first_name' => 'Alice',
            'last_name' => 'Smith',
            'email' => 'alice@example.com',
            'phone' => '1111111111',
            'password' => bcrypt('password'),
            'is_active' => true,
            'created_at' => now()->subDays(5),
        ]);
        $user1->customerProfile()->create([
            'total_spent' => 500.00,
            'total_orders' => 2,
        ]);

        // Customer 2: spent 1000, orders 1, inactive
        $user2 = User::create([
            'first_name' => 'Bob',
            'last_name' => 'Jones',
            'email' => 'bob@example.com',
            'phone' => '2222222222',
            'password' => bcrypt('password'),
            'is_active' => false,
            'created_at' => now()->subDays(2),
        ]);
        $user2->customerProfile()->create([
            'total_spent' => 1000.00,
            'total_orders' => 1,
        ]);

        // Test listing filter: active
        $response = $this->getJson('/api/admin/customers?filter[status]=active');
        $response->assertStatus(200)
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.email', 'alice@example.com');

        // Test listing filter: inactive
        $response = $this->getJson('/api/admin/customers?filter[status]=inactive');
        $response->assertStatus(200)
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.email', 'bob@example.com');

        // Test listing search
        $response = $this->getJson('/api/admin/customers?search=Jones');
        $response->assertStatus(200)
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.email', 'bob@example.com');

        // Test listing search by phone
        $response = $this->getJson('/api/admin/customers?search=1111111111');
        $response->assertStatus(200)
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.email', 'alice@example.com');

        // Test sorting: total_spent desc (Bob should be first: 1000 > 500)
        $response = $this->getJson('/api/admin/customers?sort_by=total_spent');
        $response->assertStatus(200)
            ->assertJsonPath('data.0.email', 'bob@example.com')
            ->assertJsonPath('data.1.email', 'alice@example.com');

        // Test sorting: total_orders desc (Alice should be first: 2 > 1)
        $response = $this->getJson('/api/admin/customers?sort_by=total_orders');
        $response->assertStatus(200)
            ->assertJsonPath('data.0.email', 'alice@example.com')
            ->assertJsonPath('data.1.email', 'bob@example.com');
    }

    public function test_can_get_customer_details(): void
    {
        $user = User::create([
            'first_name' => 'Charlie',
            'last_name' => 'Brown',
            'email' => 'charlie@example.com',
            'password' => bcrypt('password'),
        ]);
        $user->customerProfile()->create([
            'date_of_birth' => '1995-05-15',
            'gender' => 'Male',
            'notes' => 'Some test notes',
        ]);
        $address = $user->addresses()->create([
            'first_name' => 'Charlie',
            'last_name' => 'Brown',
            'phone' => '9999999999',
            'address_line_1' => '123 Main St',
            'city' => 'Metropolis',
            'state' => 'NY',
            'postal_code' => '10001',
            'country' => 'US',
            'is_default_shipping' => true,
        ]);

        $response = $this->getJson("/api/admin/customers/{$user->id}");

        $response->assertStatus(200)
            ->assertJsonPath('data.email', 'charlie@example.com')
            ->assertJsonPath('data.profile.date_of_birth', '1995-05-15')
            ->assertJsonPath('data.profile.notes', 'Some test notes')
            ->assertJsonCount(1, 'data.addresses')
            ->assertJsonPath('data.addresses.0.address_line_1', '123 Main St');
    }

    public function test_can_update_customer_status_and_profile(): void
    {
        $user = User::create([
            'first_name' => 'David',
            'last_name' => 'Miller',
            'email' => 'david@example.com',
            'password' => bcrypt('password'),
            'is_active' => true,
        ]);
        $user->customerProfile()->create([
            'notes' => 'Original notes',
        ]);

        $response = $this->putJson("/api/admin/customers/{$user->id}", [
            'first_name' => 'Dave',
            'is_active' => false,
            'notes' => 'Updated notes via API',
            'gender' => 'Other',
            'date_of_birth' => '1990-01-01',
            'email_subscribed' => true,
        ]);

        $response->assertStatus(200)
            ->assertJsonPath('data.first_name', 'Dave')
            ->assertJsonPath('data.is_active', false)
            ->assertJsonPath('data.profile.notes', 'Updated notes via API')
            ->assertJsonPath('data.profile.gender', 'Other')
            ->assertJsonPath('data.profile.date_of_birth', '1990-01-01')
            ->assertJsonPath('data.profile.email_subscribed', true);

        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'first_name' => 'Dave',
            'is_active' => false,
        ]);

        $this->assertDatabaseHas('customer_profiles', [
            'user_id' => $user->id,
            'notes' => 'Updated notes via API',
            'gender' => 'Other',
            'email_subscribed' => true,
        ]);
    }

    public function test_can_manage_customer_addresses_and_toggles_defaults(): void
    {
        $user = User::create([
            'first_name' => 'Emma',
            'last_name' => 'Watson',
            'email' => 'emma@example.com',
            'password' => bcrypt('password'),
        ]);

        // Add first address (marked as default shipping & billing)
        $response = $this->postJson("/api/admin/customers/{$user->id}/addresses", [
            'label' => 'Home',
            'first_name' => 'Emma',
            'last_name' => 'Watson',
            'phone' => '8888888888',
            'address_line_1' => '456 Oak Ave',
            'city' => 'Oakville',
            'state' => 'CA',
            'postal_code' => '90001',
            'country' => 'US',
            'is_default_shipping' => true,
            'is_default_billing' => true,
        ]);

        $response->assertStatus(201)
            ->assertJsonPath('label', 'Home')
            ->assertJsonPath('is_default_shipping', true)
            ->assertJsonPath('is_default_billing', true);

        $address1Id = $response->json('id');

        // Add second address (marked as default shipping)
        $response2 = $this->postJson("/api/admin/customers/{$user->id}/addresses", [
            'label' => 'Office',
            'first_name' => 'Emma',
            'last_name' => 'Watson',
            'phone' => '8888888888',
            'address_line_1' => '789 Business Blvd',
            'city' => 'Worktown',
            'state' => 'CA',
            'postal_code' => '90002',
            'country' => 'US',
            'is_default_shipping' => true,
            'is_default_billing' => false,
        ]);

        $response2->assertStatus(201)
            ->assertJsonPath('label', 'Office')
            ->assertJsonPath('is_default_shipping', true)
            ->assertJsonPath('is_default_billing', false);

        // Verify that address 1 is no longer default shipping, but remains default billing
        $this->assertDatabaseHas('addresses', [
            'id' => $address1Id,
            'is_default_shipping' => false,
            'is_default_billing' => true,
        ]);

        // Update address 2 to be default billing
        $address2Id = $response2->json('id');
        $response3 = $this->putJson("/api/admin/customers/{$user->id}/addresses/{$address2Id}", [
            'is_default_billing' => true,
        ]);

        $response3->assertStatus(200)
            ->assertJsonPath('is_default_billing', true);

        // Verify that address 1 is no longer default billing
        $this->assertDatabaseHas('addresses', [
            'id' => $address1Id,
            'is_default_billing' => false,
        ]);
    }

    public function test_enforces_address_limit_of_five(): void
    {
        $user = User::create([
            'first_name' => 'Frank',
            'last_name' => 'Castle',
            'email' => 'frank@example.com',
            'password' => bcrypt('password'),
        ]);

        // Create 5 addresses
        for ($i = 1; $i <= 5; $i++) {
            $user->addresses()->create([
                'first_name' => 'Frank',
                'last_name' => 'Castle',
                'phone' => '7777777777',
                'address_line_1' => "Street {$i}",
                'city' => 'New York',
                'state' => 'NY',
                'postal_code' => '10001',
                'country' => 'US',
            ]);
        }

        // Try to add 6th address - should return 422
        $response = $this->postJson("/api/admin/customers/{$user->id}/addresses", [
            'first_name' => 'Frank',
            'last_name' => 'Castle',
            'phone' => '7777777777',
            'address_line_1' => '6th Street',
            'city' => 'New York',
            'state' => 'NY',
            'postal_code' => '10001',
            'country' => 'US',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['address']);
    }

    public function test_can_soft_delete_address(): void
    {
        $user = User::create([
            'first_name' => 'Grace',
            'last_name' => 'Hopper',
            'email' => 'grace@example.com',
            'password' => bcrypt('password'),
        ]);
        $address = $user->addresses()->create([
            'first_name' => 'Grace',
            'last_name' => 'Hopper',
            'phone' => '6666666666',
            'address_line_1' => 'Cobol Lane',
            'city' => 'Graceville',
            'state' => 'VA',
            'postal_code' => '22001',
            'country' => 'US',
        ]);

        $response = $this->deleteJson("/api/admin/customers/{$user->id}/addresses/{$address->id}");
        $response->assertStatus(200);

        // Assert soft deleted
        $this->assertSoftDeleted('addresses', [
            'id' => $address->id,
        ]);
    }
}
