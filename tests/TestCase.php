<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use App\Models\User;
use App\Models\Role;

abstract class TestCase extends BaseTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $className = static::class;

        if ($className === \Tests\Feature\ExampleTest::class || $className === \Tests\Unit\ExampleTest::class) {
            return;
        }

        if (in_array($className, [
            \Tests\Feature\CustomerApiTest::class,
            \Tests\Feature\UserRoleApiTest::class,
            \Tests\Feature\ReturnApiTest::class,
            \Tests\Feature\AuditLogApiTest::class,
            \Tests\Feature\BlogApiTest::class,
            \Tests\Feature\CouponApiTest::class,
        ])) {
            $this->withoutMiddleware();
            return;
        }

        if ($className !== \Tests\Feature\MenuApiTest::class) {
            $admin = User::factory()->create();
            $superAdmin = Role::firstOrCreate(['name' => 'super_admin']);
            $admin->assignRole($superAdmin);
            $this->actingAs($admin);
        }
    }
}
