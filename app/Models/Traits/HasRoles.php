<?php

namespace App\Models\Traits;

use App\Models\Role;
use App\Models\Permission;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

trait HasRoles
{
    /**
     * Boot the trait and register cascade deletes.
     */
    protected static function bootHasRoles()
    {
        static::deleting(function ($model) {
            $model->roles()->detach();
        });
    }

    /**
     * Roles associated with this model.
     */
    public function roles(): BelongsToMany
    {
        return $this->belongsToMany(
            Role::class,
            'model_has_roles',
            'model_id',
            'role_id'
        )->wherePivot('model_type', self::class)
         ->withPivot('model_type');
    }

    /**
     * Assign a role to the user.
     */
    public function assignRole(...$roles)
    {
        foreach ($roles as $role) {
            if (is_string($role)) {
                $role = Role::where('name', $role)->first();
            }
            if ($role instanceof Role) {
                // Update direct role_id on model
                $this->role_id = $role->id;
                $this->save();

                // Attach if not already attached
                if (!$this->roles()->where('roles.id', $role->id)->exists()) {
                    $this->roles()->attach($role->id, ['model_type' => self::class]);
                }
            }
        }
        return $this;
    }

    /**
     * Sync roles for the user.
     */
    public function syncRoles(array $roles)
    {
        $roleIds = [];
        foreach ($roles as $role) {
            if (is_string($role)) {
                $roleObj = Role::where('name', $role)->first();
                if ($roleObj) {
                    $roleIds[] = $roleObj->id;
                }
            } elseif (is_numeric($role)) {
                $roleIds[] = (int) $role;
            } elseif ($role instanceof Role) {
                $roleIds[] = $role->id;
            }
        }

        $syncData = [];
        foreach ($roleIds as $id) {
            $syncData[$id] = ['model_type' => self::class];
        }

        $this->roles()->sync($syncData);

        // Update direct role_id
        if (!empty($roleIds)) {
            $this->role_id = $roleIds[0];
        } else {
            $this->role_id = null;
        }
        $this->save();

        return $this;
    }

    /**
     * Check if user has a specific role.
     */
    public function hasRole(string $roleName): bool
    {
        if ($this->relationLoaded('role') && $this->role) {
            if ($this->role->name === $roleName) {
                return true;
            }
        }

        if ($this->role_id) {
            $role = Role::find($this->role_id);
            if ($role && $role->name === $roleName) {
                return true;
            }
        }

        return $this->roles()->where('name', $roleName)->exists();
    }

    /**
     * Check if user has a specific permission.
     */
    public function hasPermissionTo(string $permissionName): bool
    {
        // 1. Super admin always has all permissions
        if ($this->hasRole('super_admin') || (int)$this->role_id === 1 || ($this->relationLoaded('role') && $this->role?->name === 'super_admin')) {
            return true;
        }

        // 2. Fetch user's assigned permissions
        $userPermissions = $this->roles()
            ->with('permissions')
            ->get()
            ->flatMap(fn($r) => $r->permissions->pluck('name'))
            ->toArray();

        if ($this->role_id) {
            $directRole = Role::with('permissions')->find($this->role_id);
            if ($directRole) {
                $userPermissions = array_merge($userPermissions, $directRole->permissions->pluck('name')->toArray());
            }
        }
        $userPermissions = array_unique($userPermissions);

        // 3. Direct match (e.g. 'products.create', 'categories.delete', etc.)
        if (in_array($permissionName, $userPermissions)) {
            return true;
        }

        // 4. If checking a module without dot (e.g. 'products', 'orders', 'reports')
        if (!str_contains($permissionName, '.')) {
            foreach ($userPermissions as $up) {
                if ($up === $permissionName || str_starts_with($up, $permissionName . '.')) {
                    return true;
                }
            }

            $legacyAliases = [
                'manage_products' => ['products', 'categories', 'tags', 'colors', 'sizes', 'inventory', 'stock_entry', 'inward', 'occasions', 'section_badges', 'reviews', 'coupons', 'reels', 'blog'],
                'manage_orders' => ['orders', 'returns', 'couriers', 'purchase_orders'],
                'manage_users' => ['users', 'roles', 'customers'],
                'manage_reports' => ['reports', 'reports_sales', 'reports_inventory', 'reports_customers', 'payments', 'settlements'],
                'reports' => ['reports', 'reports_sales', 'reports_inventory', 'reports_customers', 'payments', 'settlements'],
                'reports_sales' => ['reports_sales', 'reports', 'manage_reports'],
                'reports_inventory' => ['reports_inventory', 'reports', 'manage_reports'],
                'reports_customers' => ['reports_customers', 'reports', 'manage_reports'],
                'payments' => ['payments', 'reports', 'manage_reports'],
                'settlements' => ['settlements', 'reports', 'manage_reports'],
                'manage_settings' => ['settings'],
            ];

            if (isset($legacyAliases[$permissionName])) {
                foreach ($legacyAliases[$permissionName] as $mod) {
                    foreach ($userPermissions as $up) {
                        if ($up === $mod || str_starts_with($up, $mod . '.')) {
                            return true;
                        }
                    }
                }
            }
        }

        return false;
    }
}
