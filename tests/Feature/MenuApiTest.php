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

        $this->permission = Permission::firstOrCreate(
            ['name' => 'manage_orders'],
            ['description' => 'Test permission', 'module' => 'orders', 'action' => 'view']
        );

        $this->role = Role::firstOrCreate([
            'name' => 'sales_manager',
            'description' => 'Test role',
        ]);
        $this->role->permissions()->syncWithoutDetaching([$this->permission->id]);
    }

    public function test_unauthenticated_user_cannot_access_menus(): void
    {
        $response = $this->getJson('/api/admin/menus');
        $response->assertStatus(401);
    }

    public function test_super_admin_can_see_all_menus(): void
    {
        $superAdminRole = Role::firstOrCreate(['name' => 'super_admin']);
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

        Menu::create([
            'name' => 'Payments',
            'path' => '/admin/reports/payments',
            'group' => 'Reports',
            'is_active' => true,
        ]);

        $response = $this->actingAs($user)->getJson('/api/admin/menus');
        $response->assertStatus(200);

        $data = $response->json();
        $this->assertTrue(collect($data)->flatten(1)->contains('name', 'Secret Admin Panel'));
        $this->assertTrue(collect($data)->flatten(1)->contains('name', 'Public Admin Panel'));
        $this->assertTrue(collect($data)->flatten(1)->contains('name', 'Payments'));
    }

    public function test_user_with_reports_permission_can_see_reports_menus(): void
    {
        $reportPerm = Permission::firstOrCreate(['name' => 'reports.view'], ['module' => 'reports', 'action' => 'view']);
        $financeRole = Role::firstOrCreate(['name' => 'finance_lead']);
        $financeRole->permissions()->syncWithoutDetaching([$reportPerm->id]);

        $user = User::factory()->create();
        $user->assignRole($financeRole);

        Menu::create([
            'name' => 'Reports',
            'path' => '/admin/reports',
            'group' => 'Reports',
            'is_active' => true,
        ]);

        Menu::create([
            'name' => 'Payments',
            'path' => '/admin/reports/payments',
            'group' => 'Reports',
            'is_active' => true,
        ]);

        Menu::create([
            'name' => 'Settlements',
            'path' => '/admin/reports/settlements',
            'group' => 'Reports',
            'is_active' => true,
        ]);

        Menu::create([
            'name' => 'Roles & Permissions',
            'path' => '/admin/roles',
            'group' => 'Administration',
            'is_active' => true,
        ]);

        $response = $this->actingAs($user)->getJson('/api/admin/menus');
        $response->assertStatus(200);

        $data = $response->json();
        $this->assertTrue(collect($data)->flatten(1)->contains('name', 'Reports'));
        $this->assertTrue(collect($data)->flatten(1)->contains('name', 'Payments'));
        $this->assertTrue(collect($data)->flatten(1)->contains('name', 'Settlements'));
        $this->assertFalse(collect($data)->flatten(1)->contains('name', 'Roles & Permissions'));
    }

    public function test_user_without_reports_permission_cannot_see_reports_menus(): void
    {
        $catalogPerm = Permission::firstOrCreate(['name' => 'products.view'], ['module' => 'products', 'action' => 'view']);
        $catalogRole = Role::firstOrCreate(['name' => 'catalog_staff']);
        $catalogRole->permissions()->syncWithoutDetaching([$catalogPerm->id]);

        $user = User::factory()->create();
        $user->assignRole($catalogRole);

        Menu::create([
            'name' => 'Reports',
            'path' => '/admin/reports',
            'group' => 'Reports',
            'is_active' => true,
        ]);

        Menu::create([
            'name' => 'Payments',
            'path' => '/admin/reports/payments',
            'group' => 'Reports',
            'is_active' => true,
        ]);

        Menu::create([
            'name' => 'Products',
            'path' => '/admin/products',
            'group' => 'Catalog',
            'is_active' => true,
        ]);

        $response = $this->actingAs($user)->getJson('/api/admin/menus');
        $response->assertStatus(200);

        $data = $response->json();
        $this->assertTrue(collect($data)->flatten(1)->contains('name', 'Products'));
        $this->assertFalse(collect($data)->flatten(1)->contains('name', 'Reports'));
        $this->assertFalse(collect($data)->flatten(1)->contains('name', 'Payments'));
    }
}
