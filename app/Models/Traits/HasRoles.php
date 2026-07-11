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
        // First check if user is a super admin
        if ($this->hasRole('super_admin')) {
            return true;
        }

        return $this->roles()
            ->whereHas('permissions', function ($q) use ($permissionName) {
                $q->where('name', $permissionName);
            })->exists();
    }
}
