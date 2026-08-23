<?php

namespace App\Http\Controllers\Api\v1\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Menu;
use App\Models\Role;

class MenuController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();
        
        $allMenus = Menu::where('is_active', true)
                        ->orderBy('order')
                        ->get();

        $menuModuleMap = [
            'Dashboard' => null, // Always visible to authenticated admin
            'Orders' => 'orders',
            'Returns' => 'returns',
            'Couriers' => 'couriers',
            'Purchase Orders' => 'purchase_orders',
            'Products' => 'products',
            'Stock Inward' => 'inward',
            'Categories' => 'categories',
            'Product Reviews' => 'reviews',
            'Colors' => 'colors',
            'Size Master' => 'sizes',
            'Coupons' => 'coupons',
            'Insta Reels' => 'reels',
            'Blog' => 'blog',
            'Reports' => 'reports',
            'Customers' => 'customers',
            'Users' => 'users',
            'Roles & Permissions' => 'roles',
            'Settings' => 'settings',
        ];

        // Gather all user permission strings
        $userPermissions = [];
        if ($user) {
            $userPermissions = $user->roles()
                ->with('permissions')
                ->get()
                ->flatMap(fn($r) => $r->permissions->pluck('name'))
                ->toArray();

            if ($user->role_id) {
                $directRole = Role::with('permissions')->find($user->role_id);
                if ($directRole) {
                    $userPermissions = array_merge($userPermissions, $directRole->permissions->pluck('name')->toArray());
                }
            }
            $userPermissions = array_unique($userPermissions);
        }

        $isSuperAdmin = $user && ($user->hasRole('super_admin') || $user->role_id === 1);

        $allowedMenus = $allMenus->filter(function ($menu) use ($user, $isSuperAdmin, $menuModuleMap, $userPermissions) {
            // Super admin bypasses all menu restrictions
            if ($isSuperAdmin) {
                return true;
            }

            // Check role constraint if set
            if ($menu->role_id) {
                $hasRole = ($user->role_id == $menu->role_id) || 
                           $user->roles()->where('roles.id', $menu->role_id)->exists();
                if (!$hasRole) {
                    return false;
                }
            }

            $moduleKey = $menuModuleMap[$menu->name] ?? null;

            // Dashboard is always visible
            if ($menu->name === 'Dashboard' || $moduleKey === null) {
                return true;
            }

            // For any other menu, user MUST have at least one permission for this module
            $hasModulePermission = false;
            foreach ($userPermissions as $p) {
                if (
                    $p === $moduleKey || 
                    str_starts_with($p, $moduleKey . '.')
                ) {
                    $hasModulePermission = true;
                    break;
                }
            }

            return $hasModulePermission;
        })->values();

        // Group by 'group' field and remove empty groups
        $groupedMenus = $allowedMenus->groupBy('group')->filter(function ($groupItems) {
            return $groupItems->isNotEmpty();
        });

        return response()->json($groupedMenus);
    }
}
