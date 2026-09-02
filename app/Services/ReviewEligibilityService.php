<?php

namespace App\Services;

use App\Models\Order;
use App\Models\Product;
use App\Models\ProductReview;
use App\Models\Setting;
use App\Models\User;
use Carbon\Carbon;

class ReviewEligibilityService
{
    /**
     * Determine if a user is eligible to review a given product.
     *
     * @param User|null $user
     * @param Product $product
     * @return array
     */
    public function checkEligibility(?User $user, Product $product): array
    {
        // 1. Check if reviews are enabled for this product
        if (!$product->reviews_enabled) {
            return [
                'can_review' => false,
                'reason' => 'review_disabled',
                'has_reviewed' => false,
                'is_verified_purchase' => false,
                'message' => 'Reviews are currently disabled for this product.',
            ];
        }

        // 2. Check login requirement
        $loginRequired = Setting::get('login_required', 'reviews', true);
        if ($loginRequired && !$user) {
            return [
                'can_review' => false,
                'reason' => 'guest',
                'has_reviewed' => false,
                'is_verified_purchase' => false,
                'message' => 'Please sign in to share your experience with this product.',
            ];
        }

        if (!$user) {
            return [
                'can_review' => true,
                'reason' => null,
                'has_reviewed' => false,
                'is_verified_purchase' => false,
                'message' => null,
            ];
        }

        // 3. Check if user has already reviewed this product
        $existingReview = ProductReview::with('images')
            ->where('product_id', $product->id)
            ->where('user_id', $user->id)
            ->first();

        $oneReviewPerProduct = Setting::get('one_review_per_product', 'reviews', true);
        if ($existingReview && $oneReviewPerProduct) {
            return [
                'can_review' => false,
                'reason' => 'already_reviewed',
                'has_reviewed' => true,
                'is_verified_purchase' => (bool) $existingReview->is_verified_purchase,
                'review' => $existingReview,
                'message' => "You've already shared your experience with this product.",
            ];
        }

        // 4. Check verified purchase requirement if enabled in settings
        $verifiedPurchaseRequired = Setting::get('verified_purchase_required', 'reviews', false);
        $deliveredOrderRequired = Setting::get('delivered_order_required', 'reviews', false);

        // Fetch orders for this user that include this product (for verified purchase badge)
        $userOrders = Order::where('user_id', $user->id)
            ->whereHas('items', function ($query) use ($product) {
                $query->where('product_id', $product->id);
            })
            ->whereNotIn('status', ['cancelled'])
            ->orderBy('id', 'desc')
            ->get();

        if ($verifiedPurchaseRequired && $userOrders->isEmpty()) {
            return [
                'can_review' => false,
                'reason' => 'not_purchased',
                'has_reviewed' => false,
                'is_verified_purchase' => false,
                'message' => 'Only customers who have purchased this item can leave a review.',
            ];
        }

        $deliveredOrder = $userOrders->first(function ($order) {
            return $order->status === 'delivered' || !is_null($order->delivered_at);
        });

        if ($deliveredOrderRequired && !$deliveredOrder) {
            return [
                'can_review' => false,
                'reason' => 'order_not_delivered',
                'has_reviewed' => false,
                'is_verified_purchase' => false,
                'message' => 'You can review this item once your order has been delivered.',
            ];
        }

        $targetOrder = $deliveredOrder ?? $userOrders->first();
        $isVerifiedPurchase = !is_null($targetOrder);

        return [
            'can_review' => true,
            'reason' => null,
            'has_reviewed' => (bool) $existingReview,
            'is_verified_purchase' => $isVerifiedPurchase,
            'order_id' => $targetOrder?->id,
            'message' => null,
        ];
    }
}
