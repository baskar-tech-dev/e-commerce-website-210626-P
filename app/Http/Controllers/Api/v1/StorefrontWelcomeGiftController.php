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

        // Check if festival mode is active globally
        $isFestivalActive = (bool) Setting::get('is_enabled', 'festival', false);

        // Diwali reveal setting (defaults to festival mode status or true if explicitly enabled)
        $diwaliRevealRaw = $settings->get('diwali_reveal_enabled', null);
        $diwaliRevealEnabled = $diwaliRevealRaw !== null 
            ? filter_var($diwaliRevealRaw, FILTER_VALIDATE_BOOLEAN)
            : $isFestivalActive;

        $couponCode = strtoupper(trim((string) $settings->get('coupon_code', 'SAVE5')));
        $discountText = (string) $settings->get('discount_text', 'Enjoy 10% OFF');

        if ($diwaliRevealEnabled) {
            // Sanitize discount text: remove decimals, asterisks, and "Your First Order"
            $discountText = str_replace('*', '', $discountText);
            $discountText = preg_replace('/(\d+)\.00(?=%|\s|$)/', '$1', $discountText);
            $discountText = preg_replace('/(\s*(?:on|for)?\s*your\s*first\s*order)/i', '', $discountText);
            $discountText = trim($discountText) ?: 'Enjoy 10% OFF';
            $title = trim(str_replace('*', '', (string) $settings->get('title', 'A Little Diwali Magic')));
            $subtitle = trim(str_replace('*', '', (string) $settings->get('subtitle', 'A festive surprise is waiting for you.')));
        } else {
            $title = $settings->get('title', 'A Special Gift Awaits You');
            $subtitle = $settings->get('subtitle', 'Every new member deserves a warm welcome.');
        }

        // Coupon eligibility verification
        $coupon = Coupon::where('code', $couponCode)->first();
        $isCouponValid = false;
        $invalidationReason = null;
        $couponPayload = null;

        if (!$coupon) {
            $invalidationReason = 'not_found';
        } elseif (!$coupon->is_active) {
            $invalidationReason = 'inactive';
        } elseif ($coupon->starts_at && $coupon->starts_at->isFuture()) {
            $invalidationReason = 'not_started';
        } elseif ($coupon->expires_at && $coupon->expires_at->isPast()) {
            $invalidationReason = 'expired';
        } elseif (!is_null($coupon->max_uses_total) && $coupon->times_used >= $coupon->max_uses_total) {
            $invalidationReason = 'usage_limit_reached';
        } else {
            $isCouponValid = true;
            $couponPayload = [
                'code' => $coupon->code,
                'name' => $coupon->name,
                'description' => $coupon->description,
                'type' => $coupon->type,
                'value' => (float) $coupon->value,
                'min_order_value' => $coupon->min_order_value ? (float) $coupon->min_order_value : null,
                'max_discount' => $coupon->max_discount ? (float) $coupon->max_discount : null,
                'expires_at' => $coupon->expires_at ? $coupon->expires_at->toISOString() : null,
                'first_order_only' => (bool) $coupon->first_order_only,
            ];
        }

        return response()->json([
            'success' => true,
            'data' => [
                'is_enabled' => $isEnabled,
                'diwali_reveal_enabled' => $diwaliRevealEnabled,
                'coupon_code' => $couponCode,
                'discount_text' => $discountText,
                'title' => $title,
                'subtitle' => $subtitle,
                'cta_text' => $diwaliRevealEnabled ? 'REVEAL MY OFFER' : 'Open Gift',
                'is_coupon_valid' => $isCouponValid,
                'invalidation_reason' => $invalidationReason,
                'coupon' => $couponPayload,
            ],
        ]);
    }
}
