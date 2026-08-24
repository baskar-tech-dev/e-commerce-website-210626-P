<?php

namespace App\Http\Controllers;

abstract class Controller
{
    /**
     * Authorize user against a required granular permission.
     * Throws 403 Forbidden if not authorized.
     */
    protected function authorizePermission(string $permission): void
    {
        $user = auth()->user() ?? auth('sanctum')->user() ?? request()->user();

        if (!$user) {
            abort(response()->json([
                'success' => false,
                'message' => 'Unauthenticated.',
                'error_code' => 'UNAUTHENTICATED'
            ], 401));
        }

        if (!$user->hasPermissionTo($permission)) {
            abort(response()->json([
                'success' => false,
                'message' => "Access Denied. You do not have permission ({$permission}) to perform this action.",
                'error_code' => 'FORBIDDEN'
            ], 403));
        }
    }
}
