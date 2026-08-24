<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Coupon;
use Carbon\Carbon;

class CouponSeeder extends Seeder
{
    /**
     * Seed initial boutique coupons.
     */
    public function run(): void
    {
        $coupons = [
            [
                'code' => 'WELCOME10',
                'name' => 'New Customer Welcome 10% OFF',
                'description' => 'Get 10% discount on your first order at Maya Sree Fashion.',
                'type' => 'percentage',
                'value' => 10.00,
                'max_discount' => 500.00,
                'min_order_value' => 499.00,
                'max_uses_total' => 10000,
                'max_uses_per_user' => 1,
                'first_order_only' => true,
                'is_auto_apply' => false,
                'is_combinable' => false,
                'is_active' => true,
                'starts_at' => Carbon::now()->subDays(30),
                'expires_at' => Carbon::now()->addYears(2),
            ],
            [
                'code' => 'FIRST20',
                'name' => '20% OFF First Festive Order',
                'description' => 'Exclusive 20% off for first-time shoppers on silk sarees & readymade blouses.',
                'type' => 'percentage',
                'value' => 20.00,
                'max_discount' => 1000.00,
                'min_order_value' => 999.00,
                'max_uses_total' => 5000,
                'max_uses_per_user' => 1,
                'first_order_only' => true,
                'is_auto_apply' => false,
                'is_combinable' => false,
                'is_active' => true,
                'starts_at' => Carbon::now()->subDays(15),
                'expires_at' => Carbon::now()->addYears(2),
            ],
            [
                'code' => 'MSF5',
                'name' => '5% OFF WhatsApp Club',
                'description' => 'Exclusive 5% discount for Maya Sree WhatsApp Club members.',
                'type' => 'percentage',
                'value' => 5.00,
                'max_discount' => 500.00,
                'min_order_value' => 0.00,
                'max_uses_total' => 10000,
                'max_uses_per_user' => 1,
                'first_order_only' => false,
                'is_auto_apply' => false,
                'is_combinable' => false,
                'is_active' => true,
                'starts_at' => Carbon::now()->subDays(10),
                'expires_at' => Carbon::now()->addYears(2),
            ],
            [
                'code' => 'FESTIVE15',
                'name' => '15% Seasonal Festive Discount',
                'description' => 'Celebration discount for all existing and new customers.',
                'type' => 'percentage',
                'value' => 15.00,
                'max_discount' => 750.00,
                'min_order_value' => 1499.00,
                'max_uses_total' => 10000,
                'max_uses_per_user' => 5,
                'first_order_only' => false,
                'is_auto_apply' => false,
                'is_combinable' => false,
                'is_active' => true,
                'starts_at' => Carbon::now()->subDays(5),
                'expires_at' => Carbon::now()->addYears(1),
            ],
            [
                'code' => 'FREESHIP',
                'name' => 'Free Express Shipping',
                'description' => 'Free shipping across all orders for all customers.',
                'type' => 'free_shipping',
                'value' => 0.00,
                'max_discount' => 100.00,
                'min_order_value' => 799.00,
                'max_uses_total' => 10000,
                'max_uses_per_user' => 10,
                'first_order_only' => false,
                'is_auto_apply' => false,
                'is_combinable' => true,
                'is_active' => true,
                'starts_at' => Carbon::now()->subDays(5),
                'expires_at' => Carbon::now()->addYears(1),
            ],
        ];

        foreach ($coupons as $c) {
            Coupon::updateOrCreate(['code' => $c['code']], $c);
        }
    }
}
