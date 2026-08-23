<?php

namespace App\Http\Controllers\Api\v1;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\Category;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class StorefrontProductController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = Product::with(['category', 'images', 'variants'])->where('is_active', true);

        // Search query
        if ($request->has('search') && !empty($request->input('search'))) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%")
                  ->orWhereHas('category', function ($sub) use ($search) {
                      $sub->where('name', 'like', "%{$search}%");
                  });
            });
        }

        // Category Filter
        if ($request->has('category_id') && !empty($request->input('category_id'))) {
            $categoryId = $request->input('category_id');
            $categoryIds = Category::where('parent_id', $categoryId)
                ->pluck('id')
                ->push((int)$categoryId)
                ->toArray();
            $query->whereIn('category_id', $categoryIds);
        }

        // Featured Filter
        if ($request->has('is_featured')) {
            $query->where('is_featured', filter_var($request->input('is_featured'), FILTER_VALIDATE_BOOLEAN));
        }

        // New Arrival Filter
        if ($request->has('is_new_arrival')) {
            $query->where('is_new_arrival', filter_var($request->input('is_new_arrival'), FILTER_VALIDATE_BOOLEAN));
        }

        // Bestseller Filter
        if ($request->has('is_bestseller')) {
            $query->where('is_bestseller', filter_var($request->input('is_bestseller'), FILTER_VALIDATE_BOOLEAN));
        }

        // Occasion Filter
        if ($request->filled('occasion')) {
            $query->where('occasion', $request->input('occasion'));
        }

        // Badge Filter (supports assigned badge, occasion tag, name matching)
        if ($request->filled('badge')) {
            $badgeVal = trim($request->input('badge'));
            $query->where(function ($q) use ($badgeVal) {
                $q->where('badge', 'like', "%{$badgeVal}%")
                  ->orWhere('occasion', 'like', "%{$badgeVal}%")
                  ->orWhere('name', 'like', "%{$badgeVal}%")
                  ->orWhere('description', 'like', "%{$badgeVal}%");
            });
        }

        // Price Filter
        if ($request->filled('min_price')) {
            $query->where('selling_price', '>=', (float) $request->input('min_price'));
        }
        if ($request->filled('max_price')) {
            $query->where('selling_price', '<=', (float) $request->input('max_price'));
        }

        // Availability / In Stock Only Filter
        if ($request->has('in_stock_only') && filter_var($request->input('in_stock_only'), FILTER_VALIDATE_BOOLEAN)) {
            $query->whereHas('variants', function ($vQuery) {
                $vQuery->where('is_active', true)->where('stock_quantity', '>', 0);
            });
        }

        // Sorting
        $sortBy = $request->input('sort_by', 'newest');
        switch ($sortBy) {
            case 'price_low_high':
                $query->orderBy('selling_price', 'asc');
                break;
            case 'price_high_low':
                $query->orderBy('selling_price', 'desc');
                break;
            case 'rating':
                $query->orderBy('avg_rating', 'desc');
                break;
            default:
                $query->orderBy('created_at', 'desc');
                break;
        }

        $products = $query->paginate($request->input('per_page', 12));

        return response()->json([
            'success' => true,
            'data' => $products->items(),
            'meta' => [
                'current_page' => $products->currentPage(),
                'last_page' => $products->lastPage(),
                'per_page' => $products->perPage(),
                'total' => $products->total(),
            ],
        ]);
    }

    public function show(string $id): JsonResponse
    {
        $query = Product::with(['category', 'images', 'variants']);
        
        if (\Illuminate\Support\Str::isUuid($id)) {
            $product = $query->where('uuid', $id)->first();
        } elseif (is_numeric($id)) {
            $product = $query->where('id', (int)$id)->first();
        } else {
            $product = $query->where('slug', $id)->first();
        }

        if (!$product) {
            return response()->json([
                'success' => false,
                'message' => 'Product not found',
            ], 404);
        }

        if (!$product->is_active) {
            return response()->json([
                'success' => false,
                'message' => 'Product is not available',
            ], 404);
        }

        // Related Products (4 products in same category)
        $related = Product::with('images')
            ->where('category_id', $product->category_id)
            ->where('id', '!=', $product->id)
            ->where('is_active', true)
            ->limit(4)
            ->get();

        return response()->json([
            'success' => true,
            'data' => $product,
            'related' => $related,
        ]);
    }

    /**
     * Public categories list for storefront filter dropdowns.
     */
    public function categories(): JsonResponse
    {
        $categories = Category::whereNull('parent_id')
            ->where('is_active', true)
            ->with(['children' => function($query) {
                $query->where('is_active', true)->orderBy('sort_order')->orderBy('name');
            }])
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get(['id', 'name', 'slug', 'icon', 'image']);

        return response()->json([
            'success' => true,
            'data' => $categories,
        ]);
    }

    /**
     * Get the configured section badges for "The Maya Sree Edit" on Homepage.
     */
    public function getEditBadges(): JsonResponse
    {
        $raw = \App\Models\Setting::get('maya_sree_edit_badges');
        if (!empty($raw)) {
            $badges = is_string($raw) ? json_decode($raw, true) : $raw;
            if (is_array($badges) && count($badges) > 0) {
                return response()->json([
                    'success' => true,
                    'data' => $badges,
                ]);
            }
        }

        // Default Maya Sree Edit badges
        $defaultBadges = [
            ['id' => 'NEW_ARRIVALS', 'label' => 'New Arrivals', 'active' => true, 'type' => 'new_arrival'],
            ['id' => 'BEST_SELLERS', 'label' => 'Best Sellers', 'active' => true, 'type' => 'bestseller'],
            ['id' => 'TRENDING', 'label' => 'Trending', 'active' => true, 'type' => 'featured'],
            ['id' => 'PREMIUM_COLLECTION', 'label' => 'Premium Collection', 'active' => true, 'type' => 'badge', 'badge_name' => 'Premium Collection'],
            ['id' => 'DESIGNER', 'label' => 'Designer', 'active' => true, 'type' => 'badge', 'badge_name' => 'Designer'],
            ['id' => 'EMBROIDERED', 'label' => 'Embroidered', 'active' => true, 'type' => 'badge', 'badge_name' => 'Embroidered'],
            ['id' => 'MIRROR_WORK', 'label' => 'Mirror Work', 'active' => true, 'type' => 'badge', 'badge_name' => 'Mirror Work'],
            ['id' => 'STONE_WORK', 'label' => 'Stone Work', 'active' => true, 'type' => 'badge', 'badge_name' => 'Stone Work'],
            ['id' => 'FLORAL_COLLECTION', 'label' => 'Floral Collection', 'active' => true, 'type' => 'badge', 'badge_name' => 'Floral Collection'],
            ['id' => 'TEMPLE_COLLECTION', 'label' => 'Temple Collection', 'active' => true, 'type' => 'badge', 'badge_name' => 'Temple Collection'],
        ];

        return response()->json([
            'success' => true,
            'data' => $defaultBadges,
        ]);
    }
}
