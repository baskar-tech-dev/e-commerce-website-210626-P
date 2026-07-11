<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Role;
use App\Models\Permission;
use App\Models\Menu;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MenuApiTest extends TestCase
{
    use RefreshDatabase;

    protected $role;
    protected $permission;

    protected function setUp(): void
    {
        parent::setUp();

        $this->permission = Permission::create([
            'name' => 'manage_orders',
            'description' => 'Test permission',
        ]);

        $this->role = Role::create([
            'name' => 'sales_manager',
            'description' => 'Test role',
        ]);
        $this->role->permissions()->sync([$this->permission->id]);
    }

    public function test_unauthenticated_user_cannot_access_menus(): void
    {
        $response = $this->getJson('/api/admin/menus');
        $response->assertStatus(401);
    }

    public function test_super_admin_can_see_all_menus(): void
    {
        $superAdminRole = Role::create(['name' => 'super_admin']);
        $user = User::factory()->create();
        $user->assignRole($superAdminRole);

        Menu::create([
            'name' => 'Secret Admin Panel',
            'path' => '/admin/secret',
            'role_id' => $this->role->id,
            'is_active' => true,
        ]);

        Menu::create([
            'name' => 'Public Admin Panel',
            'path' => '/admin/public',
            'is_active' => true,
        ]);

        $response = $this->actingAs($user)->getJson('/api/admin/menus');
        $response->assertStatus(200);

        $data = $response->json();
        $this->assertTrue(collect($data)->flatten(1)->contains('name', 'Secret Admin Panel'));
        $this->assertTrue(collect($data)->flatten(1)->contains('name', 'Public Admin Panel'));
    }

    public function test_user_without_role_cannot_see_role_restricted_menus(): void
    {
        $user = User::factory()->create();

        Menu::create([
            'name' => 'Secret Admin Panel',
            'path' => '/admin/secret',
            'role_id' => $this->role->id,
            'is_active' => true,
        ]);

        Menu::create([
            'name' => 'Public Admin Panel',
            'path' => '/admin/public',
            'is_active' => true,
        ]);

        $response = $this->actingAs($user)->getJson('/api/admin/menus');
        $response->assertStatus(200);

        $data = $response->json();
        $this->assertFalse(collect($data)->flatten(1)->contains('name', 'Secret Admin Panel'));
        $this->assertTrue(collect($data)->flatten(1)->contains('name', 'Public Admin Panel'));
    }

    public function test_user_with_role_can_see_role_restricted_menus(): void
    {
        $user = User::factory()->create();
        $user->assignRole($this->role);

        Menu::create([
            'name' => 'Secret Admin Panel',
            'path' => '/admin/secret',
            'role_id' => $this->role->id,
            'is_active' => true,
        ]);

        Menu::create([
            'name' => 'Public Admin Panel',
            'path' => '/admin/public',
            'is_active' => true,
        ]);

        $response = $this->actingAs($user)->getJson('/api/admin/menus');
        $response->assertStatus(200);

        $data = $response->json();
        $this->assertTrue(collect($data)->flatten(1)->contains('name', 'Secret Admin Panel'));
        $this->assertTrue(collect($data)->flatten(1)->contains('name', 'Public Admin Panel'));
    }
}
