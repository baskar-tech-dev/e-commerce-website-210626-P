<?php

namespace App\Http\Controllers\Api\v1;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\ProductVariant;
use App\Models\Coupon;
use App\Models\User;
use App\Services\InventoryService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Exception;

class StorefrontCheckoutController extends Controller
{
    protected $inventoryService;

    public function __construct(InventoryService $inventoryService)
    {
        $this->inventoryService = $inventoryService;
    }

    public function placeOrder(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'shipping_first_name' => 'required|string|max:100',
            'shipping_last_name' => 'required|string|max:100',
            'shipping_phone' => 'required|string|max:20',
            'shipping_address_line_1' => 'required|string|max:255',
            'shipping_address_line_2' => 'nullable|string|max:255',
            'shipping_city' => 'required|string|max:100',
            'shipping_state' => 'required|string|max:100',
            'shipping_postal_code' => 'required|string|max:20',
            
            'payment_method' => 'required|string|max:30',
            'coupon_code' => 'nullable|string|max:50',
            
            'items' => 'required|array|min:1',
            'items.*.product_variant_id' => 'required|exists:product_variants,id',
            'items.*.quantity' => 'required|integer|min:1',
        ]);

        // Sanitize string inputs to prevent stored XSS attacks
        $validated['shipping_first_name'] = strip_tags($validated['shipping_first_name']);
        $validated['shipping_last_name'] = strip_tags($validated['shipping_last_name']);
        $validated['shipping_phone'] = strip_tags($validated['shipping_phone']);
        $validated['shipping_address_line_1'] = strip_tags($validated['shipping_address_line_1']);
        if (isset($validated['shipping_address_line_2'])) {
            $validated['shipping_address_line_2'] = strip_tags($validated['shipping_address_line_2']);
        }
        $validated['shipping_city'] = strip_tags($validated['shipping_city']);
        $validated['shipping_state'] = strip_tags($validated['shipping_state']);
        $validated['shipping_postal_code'] = strip_tags($validated['shipping_postal_code']);

        $userId = auth()->id() ?? User::first()->id;

        // Idempotency check: prevent duplicate submissions within 15 seconds
        $itemsSerialized = json_encode($validated['items']);
        $cacheKey = "checkout_lock_{$userId}_" . md5($itemsSerialized);

        if (\Illuminate\Support\Facades\Cache::has($cacheKey)) {
            return response()->json([
                'success' => false,
                'message' => 'Your checkout is already in progress. Please wait a moment.',
            ], 429);
        }

        // Lock for 15 seconds
        \Illuminate\Support\Facades\Cache::put($cacheKey, true, 15);

        DB::beginTransaction();
        try {
            $subtotal = 0;
            $totalItems = 0;
            $itemsData = [];

            // 1. Validate stock and calculate subtotal
            foreach ($validated['items'] as $itemInput) {
                $variant = ProductVariant::with('product')->findOrFail($itemInput['product_variant_id']);
                
                if ($variant->stock_quantity < $itemInput['quantity']) {
                    DB::rollBack();
                    if (isset($cacheKey)) {
                        \Illuminate\Support\Facades\Cache::forget($cacheKey);
                    }
                    return response()->json([
                        'success' => false,
                        'message' => "Insufficient stock for product variant: {$variant->sku}",
                    ], 422);
                }

                $price = $variant->selling_price ?? $variant->product->selling_price;
                $mrp = $variant->mrp ?? $variant->product->mrp;

                $subtotal += $price * $itemInput['quantity'];
                $totalItems += $itemInput['quantity'];

                $itemsData[] = [
                    'variant' => $variant,
                    'quantity' => $itemInput['quantity'],
                    'price' => $price,
                    'mrp' => $mrp,
                ];
            }

            // 2. Coupon discount logic
            $discount = 0;
            $couponModel = null;
            if (!empty($validated['coupon_code'])) {
                $couponModel = Coupon::where('code', $validated['coupon_code'])
                    ->where('is_active', true)
                    ->where(function ($q) {
                        $q->whereNull('starts_at')->orWhere('starts_at', '<=', now());
                    })
                    ->where(function ($q) {
                        $q->whereNull('expires_at')->orWhere('expires_at', '>=', now());
                    })
                    ->first();

                if ($couponModel) {
                    // Check max uses total
                    if (!is_null($couponModel->max_uses_total) && $couponModel->times_used >= $couponModel->max_uses_total) {
                        $couponModel = null;
                    }
                }

                if ($couponModel) {
                    // Check max uses per user
                    if (!is_null($couponModel->max_uses_per_user)) {
                        $userUsages = \App\Models\CouponUsage::where('coupon_id', $couponModel->id)
                            ->where('user_id', $userId)
                            ->count();
                        if ($userUsages >= $couponModel->max_uses_per_user) {
                            $couponModel = null;
                        }
                    }
                }

                if ($couponModel && $couponModel->first_order_only) {
                    // Check if user has any existing paid orders
                    $hasPreviousOrders = Order::where('user_id', $userId)
                        ->where('payment_status', 'paid')
                        ->exists();
                    if ($hasPreviousOrders) {
                        $couponModel = null;
                    }
                }

                if ($couponModel) {
                    if ($subtotal >= ($couponModel->min_order_value ?? 0)) {
                        if ($couponModel->type === 'percentage') {
                            $discount = ($subtotal * $couponModel->value) / 100;
                            if ($couponModel->max_discount) {
                                $discount = min($discount, $couponModel->max_discount);
                            }
                        } else {
                            $discount = min($couponModel->value, $subtotal);
                        }
                    }
                }
            }

            // 3. Shipping charge calculation (Free for orders >= 999, else 100)
            $shipping = ($subtotal - $discount >= 999) ? 0.00 : 100.00;
            $grandTotal = $subtotal - $discount + $shipping;

            // 4. Generate order
            $orderNumber = 'VIBE-' . strtoupper(Str::random(10));
            
            $order = Order::create([
                'uuid' => (string) Str::uuid(),
                'order_number' => $orderNumber,
                'user_id' => $userId,
                'status' => 'pending',
                'payment_status' => 'pending',
                'payment_method' => $validated['payment_method'],
                'subtotal' => $subtotal,
                'discount_amount' => $discount,
                'tax_amount' => 0.00,
                'shipping_amount' => $shipping,
                'grand_total' => $grandTotal,
                'total_items' => $totalItems,
                'currency' => 'INR',
                
                // Shipping address
                'shipping_first_name' => $validated['shipping_first_name'],
                'shipping_last_name' => $validated['shipping_last_name'],
                'shipping_phone' => $validated['shipping_phone'],
                'shipping_address_line_1' => $validated['shipping_address_line_1'],
                'shipping_address_line_2' => $validated['shipping_address_line_2'] ?? null,
                'shipping_city' => $validated['shipping_city'],
                'shipping_state' => $validated['shipping_state'],
                'shipping_postal_code' => $validated['shipping_postal_code'],
                'shipping_country' => 'IN',
                
                // Billing address (same as shipping for simplicity)
                'billing_first_name' => $validated['shipping_first_name'],
                'billing_last_name' => $validated['shipping_last_name'],
                'billing_phone' => $validated['shipping_phone'],
                'billing_address_line_1' => $validated['shipping_address_line_1'],
                'billing_address_line_2' => $validated['shipping_address_line_2'] ?? null,
                'billing_city' => $validated['shipping_city'],
                'billing_state' => $validated['shipping_state'],
                'billing_postal_code' => $validated['shipping_postal_code'],
                'billing_country' => 'IN',
                
                'ip_address' => $request->ip(),
                'user_agent' => substr($request->userAgent(), 0, 500),
            ]);

            Log::info("Order Created: #{$order->order_number}");

            // Save coupon usage details
            if ($couponModel && $discount > 0) {
                \App\Models\CouponUsage::create([
                    'coupon_id' => $couponModel->id,
                    'user_id' => $userId,
                    'order_id' => $order->id,
                    'discount_amount' => $discount,
                ]);
                $couponModel->increment('times_used');
            }

            // 5. Create order items & deduct/reserve stock ledger
            foreach ($itemsData as $item) {
                $variantName = trim(($item['variant']->size ? 'Size: ' . $item['variant']->size : '') . ($item['variant']->color ? ' Color: ' . $item['variant']->color : ''));
                OrderItem::create([
                    'order_id' => $order->id,
                    'product_id' => $item['variant']->product_id,
                    'product_variant_id' => $item['variant']->id,
                    'product_name' => $item['variant']->product->name,
                    'variant_name' => $variantName ?: null,
                    'sku' => $item['variant']->sku,
                    'quantity' => $item['quantity'],
                    'unit_mrp' => $item['mrp'],
                    'unit_price' => $item['price'],
                    'total_price' => $item['price'] * $item['quantity'],
                ]);

                if (strtolower($validated['payment_method']) !== 'cod') {
                    // Reserve stock for online payment
                    $this->inventoryService->reserveStock(
                        $item['variant']->id,
                        $item['quantity'],
                        $order->id,
                        $userId
                    );
                } else {
                    // Post sale OUT stock ledger directly for COD
                    $this->inventoryService->postLedgerEntry(
                        $item['variant']->id,
                        'SALE',
                        'OUT',
                        $item['quantity'],
                        null,
                        'Order',
                        $order->id,
                        "Storefront Sale check-out for order #{$order->order_number}",
                        $userId
                    );
                }
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Order placed successfully',
                'data' => [
                    'order_id' => $order->id,
                    'order_number' => $order->order_number,
                    'grand_total' => $order->grand_total,
                    'payment_method' => $order->payment_method,
                ],
            ]);

        } catch (Exception $e) {
            DB::rollBack();
            if (isset($cacheKey)) {
                \Illuminate\Support\Facades\Cache::forget($cacheKey);
            }
            return response()->json([
                'success' => false,
                'message' => 'Order placement failed: ' . $e->getMessage(),
            ], 500);
        }
    }
}
