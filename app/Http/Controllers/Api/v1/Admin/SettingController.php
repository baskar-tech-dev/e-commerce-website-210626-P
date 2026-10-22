<?php

namespace App\Http\Controllers\Api\v1\Admin;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use App\Services\OrderNotificationService;
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

        $warnings = [];

        foreach ($validated['settings'] as $group => $pairs) {
            if (!is_array($pairs)) continue;
            foreach ($pairs as $key => $value) {
                $type = null;
                if ($group === 'announcement' || $group === 'edit_badges' || $key === 'maya_sree_edit_badges' || ($group === 'shipping' && $key === 'state_rates')) {
                    $type = 'json';
                } elseif (($group === 'welcome_gift' && in_array($key, ['is_enabled', 'diwali_reveal_enabled'])) || ($group === 'email' && in_array($key, ['order_notification_enabled', 'daily_payment_report_enabled'])) || ($group === 'payment' && in_array($key, ['cod_active', 'cashfree_active']))) {
                    $type = 'boolean';
                } elseif ($group === 'shipping' && in_array($key, ['free_shipping_threshold', 'default_shipping_fee'])) {
                    $type = 'number';
                }

                if ($group === 'welcome_gift' && $key === 'coupon_code' && !empty($value)) {
                    $coupon = \App\Models\Coupon::where('code', strtoupper(trim((string) $value)))->first();
                    if (!$coupon) {
                        $warnings[] = "Coupon '{$value}' does not exist in the Coupon database.";
                    } elseif (!$coupon->is_active) {
                        $warnings[] = "Coupon '{$value}' is currently marked as inactive.";
                    } elseif ($coupon->expires_at && $coupon->expires_at->isPast()) {
                        $warnings[] = "Coupon '{$value}' expired on " . $coupon->expires_at->format('d M Y') . ".";
                    } elseif ($coupon->starts_at && $coupon->starts_at->isFuture()) {
                        $warnings[] = "Coupon '{$value}' is scheduled to start on " . $coupon->starts_at->format('d M Y') . ".";
                    } elseif (!is_null($coupon->max_uses_total) && $coupon->times_used >= $coupon->max_uses_total) {
                        $warnings[] = "Coupon '{$value}' has reached its maximum total usage limit ({$coupon->times_used}/{$coupon->max_uses_total}).";
                    }
                }

                Setting::set($key, $value, $group, $type);
            }
        }

        return response()->json([
            'success' => true,
            'message' => 'System settings updated successfully',
            'warnings' => $warnings,
        ]);
    }

    public function testEmail(Request $request, OrderNotificationService $notificationService): JsonResponse
    {
        $validated = $request->validate([
            'email' => 'required|email|max:150',
        ]);

        $result = $notificationService->sendTestNotification($validated['email']);

        return response()->json([
            'success' => $result['success'],
            'message' => $result['message'],
        ], $result['success'] ? 200 : 422);
    }
}
