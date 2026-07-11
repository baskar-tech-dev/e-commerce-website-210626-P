<?php

namespace App\Http\Controllers\Api\v1\Admin;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SettingController extends Controller
{
    public function index(): JsonResponse
    {
        $settings = Setting::all()->groupBy('group')->map(function ($groupSettings) {
            $mapped = [];
            foreach ($groupSettings as $s) {
                $mapped[$s->key] = $s->value;
            }
            return $mapped;
        });

        return response()->json([
            'success' => true,
            'data' => $settings,
        ]);
    }

    public function updateBatch(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'settings' => 'required|array', // e.g. ['general' => ['store_name' => 'My Store']]
        ]);

        foreach ($validated['settings'] as $group => $pairs) {
            if (!is_array($pairs)) continue;
            foreach ($pairs as $key => $value) {
                Setting::set($key, $value, $group);
            }
        }

        return response()->json([
            'success' => true,
            'message' => 'System settings updated successfully',
        ]);
    }
}
