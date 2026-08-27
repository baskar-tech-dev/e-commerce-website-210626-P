<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckPermission
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, string $permission): Response
    {
        $user = $request->user();

        if (!$user) {
            return response()->json(['message' => 'Unauthenticated.'], 401);
        }

        // Super admin has universal access to all endpoints
        if ($user->hasRole('super_admin') || (int)$user->role_id === 1 || ($user->relationLoaded('role') && $user->role?->name === 'super_admin')) {
            return $next($request);
        }

        if (!$user->hasPermissionTo($permission)) {
            return response()->json(['message' => 'Forbidden. You do not have the required permission.'], 403);
        }

        return $next($request);
    }
}
