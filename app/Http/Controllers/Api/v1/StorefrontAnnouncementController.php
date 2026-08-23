<?php

namespace App\Http\Controllers\Api\v1;

use App\Http\Controllers\Controller;
use App\Models\Announcement;
use App\Models\Setting;
use Illuminate\Http\JsonResponse;

class StorefrontAnnouncementController extends Controller
{
    public function index(): JsonResponse
    {
        // Seed default items if announcements table is empty
        if (Announcement::count() === 0) {
            Announcement::create([
                'text' => '🚚 Free Shipping on Orders Above ₹1999',
                'link' => '/shop',
                'is_active' => true,
                'sort_order' => 1,
            ]);
            Announcement::create([
                'text' => '🔄 Easy 7-Day Exchange & Hassle-free Returns',
                'link' => '/refund-policy',
                'is_active' => true,
                'sort_order' => 2,
            ]);
            Announcement::create([
                'text' => '✨ Special Festive Discount: Use Code MAYASREE10 for 10% Off!',
                'link' => '/shop',
                'is_active' => true,
                'sort_order' => 3,
            ]);
        }

        // Fetch active announcements from database table
        $activeAnnouncements = Announcement::active()
            ->orderBy('sort_order', 'asc')
            ->orderBy('id', 'desc')
            ->get();

        // Retrieve bar configuration settings
        $configSetting = Setting::where('group', 'announcement')->where('key', 'config')->first();
        if (!$configSetting) {
            $configSetting = Setting::create([
                'group' => 'announcement',
                'key' => 'config',
                'type' => 'json',
                'value' => [
                    'is_enabled' => true,
                    'mode' => 'slide', // marquee, slide, fade
                    'speed' => 10,
                    'background_color' => '#6E1F3A', // Deep Maroon
                    'text_color' => '#FFFFFF', // Warm White
                    'is_sticky' => true
                ],
                'description' => 'Announcement bar configuration parameters'
            ]);
        }

        $configData = $configSetting->value ?? [];
        $configData['active_count'] = $activeAnnouncements->count();

        return response()->json([
            'success' => true,
            'items' => $activeAnnouncements,
            'config' => $configData,
        ]);
    }
}
