<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureStaffRole
{
    /**
     * Handle an incoming request.
     * Ensure the authenticated user has an assigned administrative/staff role.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthenticated.',
                'error_code' => 'UNAUTHENTICATED'
            ], 401);
        }

        // Check if user is staff/admin or has assigned role/permissions
        $isStaff = $user->hasRole('super_admin')
            || $user->hasRole('admin')
            || $user->hasRole('product_owner')
            || $user->hasRole('sales_manager')
            || $user->hasRole('editor')
            || $user->hasRole('staff')
            || ($user->role_id && (int)$user->role_id !== 6 && in_array((int)$user->role_id, [1, 2, 3, 4, 5, 7]))
            || count($user->roles()->with('permissions')->get()->flatMap(fn($r) => $r->permissions)->toArray()) > 0;

        if (!$isStaff) {
            return response()->json([
                'success' => false,
                'message' => 'Access Denied. The admin dashboard is restricted to role-assigned personnel only.',
                'error_code' => 'FORBIDDEN'
            ], 403);
        }

        return $next($request);
    }
}
