<?php

namespace Tests\Feature;

use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class StorefrontBranchTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_fetch_default_storefront_branches(): void
    {
        $response = $this->getJson('/api/storefront/branches');

        $response->assertStatus(200);
        $response->assertJsonPath('success', true);
        $data = $response->json('data');
        $this->assertIsArray($data);
        $this->assertCount(3, $data);
        $this->assertEquals('Maya Sree Fashion - Flagship Boutique & Studio', $data[0]['name']);
        $this->assertArrayHasKey('map_embed_url', $data[0]);
    }

    public function test_admin_can_update_branches(): void
    {
        $adminRole = Role::firstOrCreate(['name' => 'super_admin'], [
            'display_name' => 'Super Admin',
            'is_system' => true,
        ]);

        $admin = User::create([
            'uuid' => (string) Str::uuid(),
            'first_name' => 'Admin',
            'last_name' => 'User',
            'name' => 'Admin User',
            'email' => 'admin_' . uniqid() . '@mayasree.com',
            'phone' => '9944285100',
            'password' => bcrypt('Admin@123'),
            'role_id' => $adminRole->id,
            'is_active' => true,
        ]);

        $updateResponse = $this->actingAs($admin, 'sanctum')->postJson('/api/settings/branches', [
            'branches' => [
                [
                    'name' => 'Maya Sree Fashion - Main Flagship Store',
                    'branch_tag' => 'Exclusive Boutique',
                    'address' => 'No. 10, Premier Avenue, Solipalayam, Tiruppur – 641652',
                    'city' => 'Tiruppur',
                    'state' => 'Tamil Nadu',
                    'postal_code' => '641652',
                    'phone' => '+91 99442 85102',
                    'email' => 'boutique@mayasreefashion.com',
                    'working_hours' => 'Mon – Sat: 9:00 AM – 8:30 PM',
                    'map_embed_url' => 'https://maps.google.com/maps?q=Solipalayam+Tiruppur&output=embed',
                    'map_url' => 'https://maps.google.com/?q=Solipalayam+Tiruppur',
                    'is_primary' => true,
                ]
            ],
        ]);

        $updateResponse->assertStatus(200);
        $updateResponse->assertJsonPath('success', true);

        // Verify public storefront returns the updated branch
        $publicRes = $this->getJson('/api/storefront/branches');
        $publicRes->assertStatus(200);
        $this->assertCount(1, $publicRes->json('data'));
        $this->assertEquals('Maya Sree Fashion - Main Flagship Store', $publicRes->json('data.0.name'));
    }
}
