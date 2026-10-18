<?php

namespace App\Http\Controllers\Api\v1\Admin;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class FestivalModeController extends Controller
{
    const GROUP = 'festival';

    /**
     * Get festival mode settings for admin.
     */
    public function show(): JsonResponse
    {
        $settings = [
            'is_enabled' => (bool) Setting::get('is_enabled', self::GROUP, false),
            'festival_key' => Setting::get('festival_key', self::GROUP, 'diwali'),
            'festival_name' => Setting::get('festival_name', self::GROUP, 'Diwali Grand Celebration'),
            'hero_desktop_image' => Setting::get('hero_desktop_image', self::GROUP, ''),
            'hero_mobile_image' => Setting::get('hero_mobile_image', self::GROUP, ''),
            'hero_overlay_opacity' => (float) Setting::get('hero_overlay_opacity', self::GROUP, 0.2),
            'ornaments_enabled' => (bool) Setting::get('ornaments_enabled', self::GROUP, true),
            'ornament_type' => Setting::get('ornament_type', self::GROUP, 'diyas_toran'),
            'splash_enabled' => (bool) Setting::get('splash_enabled', self::GROUP, true),
            'splash_greeting' => Setting::get('splash_greeting', self::GROUP, 'Wishing You a Joyous Festive Season'),
            'splash_style' => Setting::get('splash_style', self::GROUP, 'maroon_gold'),
            'header_badge_text' => Setting::get('header_badge_text', self::GROUP, 'FESTIVAL & WEDDING SPECIAL'),
            'section_header_tag' => Setting::get('section_header_tag', self::GROUP, 'FRESH OFF THE RUNWAY • FESTIVE EDIT'),
            'section_header_motif' => Setting::get('section_header_motif', self::GROUP, 'diya'),
            'product_badges_enabled' => (bool) Setting::get('product_badges_enabled', self::GROUP, true),
            'product_badge_icon' => Setting::get('product_badge_icon', self::GROUP, '✨'),
            'product_badge_style' => Setting::get('product_badge_style', self::GROUP, 'maroon_gold'),
            'product_badge_prefix' => Setting::get('product_badge_prefix', self::GROUP, ''),
        ];

        return response()->json([
            'success' => true,
            'data' => $settings,
        ]);
    }

    /**
     * Update festival settings.
     */
    public function update(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'is_enabled' => 'required|boolean',
            'festival_key' => 'nullable|string|max:50',
            'festival_name' => 'nullable|string|max:100',
            'hero_desktop_image' => 'nullable|string|max:1000',
            'hero_mobile_image' => 'nullable|string|max:1000',
            'hero_overlay_opacity' => 'nullable|numeric|min:0|max:1',
            'ornaments_enabled' => 'nullable|boolean',
            'ornament_type' => 'nullable|string|max:50',
            'splash_enabled' => 'nullable|boolean',
            'splash_greeting' => 'nullable|string|max:200',
            'splash_style' => 'nullable|string|max:50',
            'header_badge_text' => 'nullable|string|max:100',
            'section_header_tag' => 'nullable|string|max:100',
            'section_header_motif' => 'nullable|string|max:50',
            'product_badges_enabled' => 'nullable|boolean',
            'product_badge_icon' => 'nullable|string|max:20',
            'product_badge_style' => 'nullable|string|max:50',
            'product_badge_prefix' => 'nullable|string|max:50',
        ]);

        Setting::set('is_enabled', $validated['is_enabled'], self::GROUP, 'boolean');

        if (array_key_exists('festival_key', $validated)) {
            Setting::set('festival_key', $validated['festival_key'] ?? 'diwali', self::GROUP, 'text');
        }
        if (array_key_exists('festival_name', $validated)) {
            Setting::set('festival_name', $validated['festival_name'] ?? '', self::GROUP, 'text');
        }
        if (array_key_exists('hero_desktop_image', $validated)) {
            Setting::set('hero_desktop_image', $validated['hero_desktop_image'] ?? '', self::GROUP, 'text');
        }
        if (array_key_exists('hero_mobile_image', $validated)) {
            Setting::set('hero_mobile_image', $validated['hero_mobile_image'] ?? '', self::GROUP, 'text');
        }
        if (array_key_exists('hero_overlay_opacity', $validated)) {
            Setting::set('hero_overlay_opacity', $validated['hero_overlay_opacity'] ?? 0.2, self::GROUP, 'number');
        }
        if (array_key_exists('ornaments_enabled', $validated)) {
            Setting::set('ornaments_enabled', (bool) ($validated['ornaments_enabled'] ?? true), self::GROUP, 'boolean');
        }
        if (array_key_exists('ornament_type', $validated)) {
            Setting::set('ornament_type', $validated['ornament_type'] ?? 'diyas_toran', self::GROUP, 'text');
        }
        if (array_key_exists('splash_enabled', $validated)) {
            Setting::set('splash_enabled', (bool) ($validated['splash_enabled'] ?? true), self::GROUP, 'boolean');
        }
        if (array_key_exists('splash_greeting', $validated)) {
            Setting::set('splash_greeting', $validated['splash_greeting'] ?? 'Wishing You a Joyous Festive Season', self::GROUP, 'text');
        }
        if (array_key_exists('splash_style', $validated)) {
            Setting::set('splash_style', $validated['splash_style'] ?? 'maroon_gold', self::GROUP, 'text');
        }
        if (array_key_exists('header_badge_text', $validated)) {
            Setting::set('header_badge_text', $validated['header_badge_text'] ?? 'FESTIVAL & WEDDING SPECIAL', self::GROUP, 'text');
        }
        if (array_key_exists('section_header_tag', $validated)) {
            Setting::set('section_header_tag', $validated['section_header_tag'] ?? 'FRESH OFF THE RUNWAY • FESTIVE EDIT', self::GROUP, 'text');
        }
        if (array_key_exists('section_header_motif', $validated)) {
            Setting::set('section_header_motif', $validated['section_header_motif'] ?? 'diya', self::GROUP, 'text');
        }
        if (array_key_exists('product_badges_enabled', $validated)) {
            Setting::set('product_badges_enabled', (bool) ($validated['product_badges_enabled'] ?? true), self::GROUP, 'boolean');
        }
        if (array_key_exists('product_badge_icon', $validated)) {
            Setting::set('product_badge_icon', $validated['product_badge_icon'] ?? '✨', self::GROUP, 'text');
        }
        if (array_key_exists('product_badge_style', $validated)) {
            Setting::set('product_badge_style', $validated['product_badge_style'] ?? 'maroon_gold', self::GROUP, 'text');
        }
        if (array_key_exists('product_badge_prefix', $validated)) {
            Setting::set('product_badge_prefix', $validated['product_badge_prefix'] ?? '', self::GROUP, 'text');
        }

        return response()->json([
            'success' => true,
            'message' => 'Festival Mode settings updated successfully.',
            'data' => [
                'is_enabled' => (bool) Setting::get('is_enabled', self::GROUP, false),
                'festival_key' => Setting::get('festival_key', self::GROUP, 'diwali'),
            ],
        ]);
    }

    /**
     * Upload festive hero banner (desktop or mobile).
     */
    public function uploadHeroBanner(Request $request): JsonResponse
    {
        $request->validate([
            'image' => 'required|image|mimes:jpeg,png,jpg,webp,avif|max:10240', // max 10MB
            'type' => 'required|string|in:desktop,mobile',
        ]);

        if ($request->hasFile('image')) {
            $file = $request->file('image');
            $type = $request->input('type');
            $filename = 'festival_hero_' . $type . '_' . time() . '_' . Str::random(6) . '.' . $file->getClientOriginalExtension();

            $path = $file->storeAs('festival', $filename, 'public');
            $url = Storage::url($path);

            $key = $type === 'mobile' ? 'hero_mobile_image' : 'hero_desktop_image';
            Setting::set($key, $url, self::GROUP, 'text');

            return response()->json([
                'success' => true,
                'message' => ucfirst($type) . ' festival hero banner uploaded successfully.',
                'url' => $url,
                'type' => $type,
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => 'No image file uploaded.',
        ], 400);
    }
}
