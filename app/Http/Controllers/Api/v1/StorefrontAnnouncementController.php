<?php

namespace App\Http\Controllers\Api\v1;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use Illuminate\Http\JsonResponse;

class StorefrontAnnouncementController extends Controller
{
    public function index(): JsonResponse
    {
        $itemsSetting = Setting::where('group', 'announcement')->where('key', 'items')->first();
        if (!$itemsSetting) {
            $itemsSetting = Setting::create([
                'group' => 'announcement',
                'key' => 'items',
                'type' => 'json',
                'value' => [
                    ['id' => 1, 'text' => '🚚 Free Shipping Above ₹999 across South India', 'link' => '/shop', 'is_active' => true],
                    ['id' => 2, 'text' => '🔄 Easy 7-Day Exchange & Hassle-free Returns', 'link' => '/refund-policy', 'is_active' => true],
                    ['id' => 3, 'text' => '✨ Special Festive Discount: Use Code MAYASREE10 for 10% Off!', 'link' => '/shop', 'is_active' => true]
                ],
                'description' => 'List of storefront announcements'
            ]);
        }

        $configSetting = Setting::where('group', 'announcement')->where('key', 'config')->first();
        if (!$configSetting) {
            $configSetting = Setting::create([
                'group' => 'announcement',
                'key' => 'config',
                'type' => 'json',
                'value' => [
                    'is_enabled' => true,
                    'mode' => 'marquee', // marquee, slide, fade
                    'speed' => 10, // speed parameter
                    'background_color' => '#493b54', // Deep Maroon
                    'text_color' => '#FFFDF9', // Warm White
                    'is_sticky' => true
                ],
                'description' => 'Announcement bar configuration parameters'
            ]);
        }

        return response()->json([
            'success' => true,
            'items' => $itemsSetting->value ?? [],
            'config' => $configSetting->value ?? []
        ]);
    }
}
