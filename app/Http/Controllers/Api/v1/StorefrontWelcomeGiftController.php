<?php

namespace App\Http\Controllers\Api\v1;

use App\Http\Controllers\Controller;
use App\Models\Coupon;
use App\Models\Setting;
use Illuminate\Http\JsonResponse;

class StorefrontWelcomeGiftController extends Controller
{
    public function index(): JsonResponse
    {
        $settings = Setting::where('group', 'welcome_gift')->get()->pluck('value', 'key');

        $isEnabledRaw = $settings->get('is_enabled', true);
        $isEnabled = filter_var($isEnabledRaw, FILTER_VALIDATE_BOOLEAN);

        $couponCode = $settings->get('coupon_code', 'WELCOME10');
        $discountText = $settings->get('discount_text', 'Enjoy 10% OFF Your First Order');
        $title = $settings->get('title', 'A Special Gift Awaits You');
        $subtitle = $settings->get('subtitle', 'Every new member deserves a warm welcome.');

        $coupon = Coupon::where('code', $couponCode)->where('is_active', true)->first();

        return response()->json([
            'success' => true,
            'data' => [
                'is_enabled' => $isEnabled,
                'coupon_code' => $couponCode,
                'discount_text' => $discountText,
                'title' => $title,
                'subtitle' => $subtitle,
                'coupon' => $coupon,
            ],
        ]);
    }
}
