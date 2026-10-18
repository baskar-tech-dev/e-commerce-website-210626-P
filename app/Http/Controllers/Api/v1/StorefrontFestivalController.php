<?php

namespace App\Http\Controllers\Api\v1;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use Illuminate\Http\JsonResponse;

class StorefrontFestivalController extends Controller
{
    const GROUP = 'festival';

    /**
     * Get active festival mode configuration for storefront.
     */
    public function index(): JsonResponse
    {
        $isEnabled = (bool) Setting::get('is_enabled', self::GROUP, false);

        $data = [
            'is_enabled' => $isEnabled,
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
            'data' => $data,
        ]);
    }
}
