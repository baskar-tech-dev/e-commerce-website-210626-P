<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Role;
use App\Models\Permission;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UserRoleApiTest extends TestCase
{
    use RefreshDatabase;

    protected $adminUser;
    protected $role;
    protected $permission;

    protected function setUp(): void
    {
        parent::setUp();

        $this->adminUser = User::factory()->create([
            'email' => 'admin@test.com',
        ]);

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

    public function test_has_roles_trait_functions(): void
    {
        $user = User::factory()->create();

        // test assign
        $user->assignRole($this->role);
        $this->assertTrue($user->hasRole('sales_manager'));

        // test permission check
        $this->assertTrue($user->hasPermissionTo('manage_orders'));
        $this->assertFalse($user->hasPermissionTo('manage_settings'));

        // test sync
        $r2 = Role::create(['name' => 'editor']);
        $user->syncRoles([$r2->id]);
        $this->assertTrue($user->hasRole('editor'));
        $this->assertFalse($user->hasRole('sales_manager'));
    }

    public function test_can_list_permissions(): void
    {
        $response = $this->getJson('/api/admin/permissions');
        $response->assertStatus(200)
            ->assertJsonPath('success', true)
            ->assertJsonCount(1, 'data');
    }

    public function test_can_list_roles(): void
    {
        $response = $this->getJson('/api/admin/roles');
        $response->assertStatus(200)
            ->assertJsonPath('success', true)
            ->assertJsonCount(1, 'data');
    }

    public function test_can_create_role_with_permissions(): void
    {
        $response = $this->postJson('/api/admin/roles', [
            'name' => 'inventory_helper',
            'description' => 'Help with POs',
            'permissions' => [$this->permission->id],
        ]);

        $response->assertStatus(200);
        $this->assertDatabaseHas('roles', ['name' => 'inventory_helper']);
        $this->assertDatabaseHas('role_has_permissions', ['permission_id' => $this->permission->id]);
    }

    public function test_can_list_staff_users(): void
    {
        $staffUser = User::factory()->create();
        $staffUser->assignRole($this->role);

        // Standard customer (with profile) is excluded if they have no role
        $customer = User::factory()->create();
        $customer->customerProfile()->create(['total_spent' => 0]);

        $response = $this->getJson('/api/admin/users');
        $response->assertStatus(200)
            ->assertJsonPath('success', true)
            ->assertJsonCount(2, 'data'); // adminUser + staffUser
    }

    public function test_can_create_staff_user_with_roles(): void
    {
        $response = $this->postJson('/api/admin/users', [
            'first_name' => 'Bob',
            'last_name' => 'Sponge',
            'email' => 'bob@test.com',
            'password' => 'secret123',
            'roles' => [$this->role->id],
        ]);

        $response->assertStatus(200);
        $this->assertDatabaseHas('users', ['email' => 'bob@test.com']);
        $newUser = User::where('email', 'bob@test.com')->first();
        $this->assertTrue($newUser->hasRole('sales_manager'));
    }

    public function test_can_update_staff_user(): void
    {
        $staff = User::factory()->create(['first_name' => 'Tim']);
        $staff->assignRole($this->role);

        $response = $this->putJson("/api/admin/users/{$staff->id}", [
            'first_name' => 'Timothy',
            'last_name' => 'Smith',
            'email' => $staff->email,
            'roles' => [$this->role->id],
        ]);

        $response->assertStatus(200);
        $this->assertEquals('Timothy', $staff->fresh()->first_name);
    }

    public function test_can_delete_staff_user(): void
    {
        $staff = User::factory()->create();
        $staff->assignRole($this->role);

        $response = $this->deleteJson("/api/admin/users/{$staff->id}");
        $response->assertStatus(200);

        $this->assertSoftDeleted('users', [
            'id' => $staff->id,
        ]);
    }
}
