<?php

namespace App\Http\Controllers\Api\v1\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Menu;

class MenuController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();
        
        $allMenus = Menu::where('is_active', true)
                        ->orderBy('order')
                        ->get();

        $allowedMenus = $allMenus->filter(function ($menu) use ($user) {
            // Super admin bypasses all menu restrictions
            if ($user->hasRole('super_admin')) {
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

            // Check permission constraint if set
            if ($menu->permission_name) {
                if (!$user->hasPermissionTo($menu->permission_name)) {
                    return false;
                }
            }

            return true;
        })->values();

        // Group by 'group' field if needed
        $groupedMenus = $allowedMenus->groupBy('group');

        return response()->json($groupedMenus);
    }
}
