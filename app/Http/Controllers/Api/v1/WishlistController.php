<?php

namespace App\Http\Controllers\Api\v1;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\Wishlist;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class WishlistController extends Controller
{
    /**
     * Get the authenticated user's wishlist.
     */
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();

        if (!$user) {
            return response()->json(['success' => false, 'message' => 'Unauthenticated'], 401);
        }

        $items = Wishlist::with(['product' => function ($q) {
            $q->with(['images' => function ($imgQuery) {
                $imgQuery->orderBy('is_primary', 'desc');
            }]);
        }])
        ->where('user_id', $user->id)
        ->latest()
        ->get();

        $formatted = $items->map(function ($item) {
            $product = $item->product;
            if (!$product) {
                return null;
            }
            $primaryImage = $product->images->first()?->image_path ?? '/asset/profile/logo.png';

            return [
                'id' => $product->id,
                'uuid' => $product->uuid,
                'name' => $product->name,
                'selling_price' => $product->selling_price,
                'mrp' => $product->mrp,
                'image' => $primaryImage,
                'in_stock' => $product->stock_quantity > 0,
                'added_at' => $item->created_at,
            ];
        })->filter()->values();

        return response()->json([
            'success' => true,
            'data' => $formatted,
        ]);
    }

    /**
     * Add a product to the wishlist.
     */
    public function store(Request $request): JsonResponse
    {
        $user = $request->user();

        if (!$user) {
            return response()->json(['success' => false, 'message' => 'Unauthenticated'], 401);
        }

        $validated = $request->validate([
            'product_id' => 'required',
        ]);

        $product = Product::where('id', $validated['product_id'])
            ->orWhere('uuid', $validated['product_id'])
            ->first();

        if (!$product) {
            return response()->json([
                'success' => false,
                'message' => 'Product not found',
            ], 404);
        }

        $wishlist = Wishlist::firstOrCreate([
            'user_id' => $user->id,
            'product_id' => $product->id,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Added to wishlist',
            'data' => $wishlist,
        ]);
    }

    /**
     * Remove a product from the wishlist.
     */
    public function destroy(string $productId, Request $request): JsonResponse
    {
        $user = $request->user();

        if (!$user) {
            return response()->json(['success' => false, 'message' => 'Unauthenticated'], 401);
        }

        $product = Product::where('id', $productId)
            ->orWhere('uuid', $productId)
            ->first();

        if (!$product) {
            return response()->json([
                'success' => false,
                'message' => 'Product not found',
            ], 404);
        }

        Wishlist::where('user_id', $user->id)
            ->where('product_id', $product->id)
            ->delete();

        return response()->json([
            'success' => true,
            'message' => 'Removed from wishlist',
        ]);
    }

    /**
     * Merge guest wishlist items into user's DB wishlist.
     */
    public function merge(Request $request): JsonResponse
    {
        $user = $request->user();

        if (!$user) {
            return response()->json(['success' => false, 'message' => 'Unauthenticated'], 401);
        }

        $validated = $request->validate([
            'items' => 'required|array',
            'items.*' => 'required',
        ]);

        foreach ($validated['items'] as $item) {
            $prodId = is_array($item) ? ($item['id'] ?? $item['product_id'] ?? null) : $item;
            if (!$prodId) continue;

            $product = Product::where('id', $prodId)
                ->orWhere('uuid', $prodId)
                ->first();

            if ($product) {
                Wishlist::firstOrCreate([
                    'user_id' => $user->id,
                    'product_id' => $product->id,
                ]);
            }
        }

        return $this->index($request);
    }
}
