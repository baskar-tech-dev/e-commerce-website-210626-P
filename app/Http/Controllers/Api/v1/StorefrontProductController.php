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
        try {
            $query = Product::with(['category', 'images', 'variants'])->where('is_active', true);

            // Search query
            if ($request->has('search') && !empty($request->input('search'))) {
                $search = trim($request->input('search'));
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
            if ($request->has('is_featured') && \Illuminate\Support\Facades\Schema::hasColumn('products', 'is_featured')) {
                $query->where('is_featured', filter_var($request->input('is_featured'), FILTER_VALIDATE_BOOLEAN));
            }

            // New Arrival Filter
            if ($request->has('is_new_arrival') && \Illuminate\Support\Facades\Schema::hasColumn('products', 'is_new_arrival')) {
                $query->where('is_new_arrival', filter_var($request->input('is_new_arrival'), FILTER_VALIDATE_BOOLEAN));
            }

            // Bestseller Filter
            if ($request->has('is_bestseller') && \Illuminate\Support\Facades\Schema::hasColumn('products', 'is_bestseller')) {
                $query->where('is_bestseller', filter_var($request->input('is_bestseller'), FILTER_VALIDATE_BOOLEAN));
            }

            // Occasion Filter (supports comma-separated multi-select values)
            if ($request->filled('occasion')) {
                $occVal = trim($request->input('occasion'));
                $hasOcc = \Illuminate\Support\Facades\Schema::hasColumn('products', 'occasion');
                $hasBadge = \Illuminate\Support\Facades\Schema::hasColumn('products', 'badge');
                $query->where(function ($q) use ($occVal, $hasOcc, $hasBadge) {
                    if ($hasOcc) {
                        $q->where('occasion', 'like', "%{$occVal}%");
                    }
                    if ($hasBadge) {
                        $hasOcc ? $q->orWhere('badge', 'like', "%{$occVal}%") : $q->where('badge', 'like', "%{$occVal}%");
                    }
                    $q->orWhere('name', 'like', "%{$occVal}%");
                });
            }

            // Badge Filter (supports assigned badge, occasion tag, name matching)
            if ($request->filled('badge')) {
                $badgeVal = trim($request->input('badge'));
                $hasOcc = \Illuminate\Support\Facades\Schema::hasColumn('products', 'occasion');
                $hasBadge = \Illuminate\Support\Facades\Schema::hasColumn('products', 'badge');
                $query->where(function ($q) use ($badgeVal, $hasOcc, $hasBadge) {
                    if ($hasBadge) {
                        $q->where('badge', 'like', "%{$badgeVal}%");
                    }
                    if ($hasOcc) {
                        $hasBadge ? $q->orWhere('occasion', 'like', "%{$badgeVal}%") : $q->where('occasion', 'like', "%{$badgeVal}%");
                    }
                    $q->orWhere('name', 'like', "%{$badgeVal}%")
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

            $perPage = min(max((int) $request->input('per_page', 12), 1), 100);
            $products = $query->paginate($perPage);

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
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::error('StorefrontProductController@index failed: ' . $e->getMessage() . "\n" . $e->getTraceAsString());
            return response()->json([
                'success' => false,
                'message' => 'Failed to load products',
                'error_code' => 'SERVER_ERROR'
            ], 500);
        }
    }

    public function show(string $id): JsonResponse
    {
        try {
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

            // Frequently Bought Together (2 complementary products with variants & images)
            $boughtTogether = Product::with(['images', 'variants', 'category'])
                ->where('id', '!=', $product->id)
                ->where('is_active', true)
                ->when($product->category_id, function ($q) use ($product) {
                    $q->where('category_id', $product->category_id);
                })
                ->limit(2)
                ->get();

            if ($boughtTogether->count() < 2) {
                $fallback = Product::with(['images', 'variants', 'category'])
                    ->where('id', '!=', $product->id)
                    ->whereNotIn('id', $boughtTogether->pluck('id'))
                    ->where('is_active', true)
                    ->limit(2 - $boughtTogether->count())
                    ->get();
                $boughtTogether = $boughtTogether->merge($fallback);
            }

            // More Products For You (Curated Recommendation Grid)
            $moreForYou = Product::with(['images', 'variants', 'category'])
                ->where('id', '!=', $product->id)
                ->where('is_active', true)
                ->inRandomOrder()
                ->limit(8)
                ->get();

            // Related Products (4 products in same category)
            $related = Product::with(['images', 'variants', 'category'])
                ->where('category_id', $product->category_id)
                ->where('id', '!=', $product->id)
                ->where('is_active', true)
                ->limit(4)
                ->get();

            return response()->json([
                'success' => true,
                'data' => $product,
                'bought_together' => $boughtTogether,
                'more_for_you' => $moreForYou,
                'related' => $related,
            ]);
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::error('StorefrontProductController@show failed: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to load product details',
                'error_code' => 'SERVER_ERROR'
            ], 500);
        }
    }

    /**
     * Public categories list for storefront filter dropdowns.
     */
    public function categories(): JsonResponse
    {
        try {
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
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::error('StorefrontProductController@categories failed: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'data' => [],
            ], 500);
        }
    }

    /**
     * Get the configured section badges for "The Maya Sree Edit" on Homepage.
     */
    public function getEditBadges(): JsonResponse
    {
        try {
            if (!\Illuminate\Support\Facades\Schema::hasTable('section_badges')) {
                return response()->json([
                    'success' => true,
                    'data' => [],
                ]);
            }

            $badges = \App\Models\SectionBadge::where('is_active', true)
                ->orderBy('sort_order', 'asc')
                ->orderBy('id', 'asc')
                ->get()
                ->map(function ($b) {
                    return [
                        'id' => $b->id,
                        'label' => $b->title,
                        'type' => $b->filter_type,
                        'badge_name' => $b->badge_key ?: $b->title,
                        'active' => (bool)$b->is_active,
                        'sort_order' => $b->sort_order,
                    ];
                });

            return response()->json([
                'success' => true,
                'data' => $badges,
            ]);
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::error('StorefrontProductController@getEditBadges failed: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'data' => [],
            ]);
        }
    }

    /**
     * Get active occasions from database for Homepage "Shop by Occasion" & Product filters.
     */
    public function getOccasions(): JsonResponse
    {
        try {
            if (!\Illuminate\Support\Facades\Schema::hasTable('occasions')) {
                return response()->json([
                    'success' => true,
                    'data' => [],
                ]);
            }

            $occasions = \App\Models\Occasion::where('is_active', true)
                ->orderBy('sort_order', 'asc')
                ->orderBy('id', 'asc')
                ->get();

            return response()->json([
                'success' => true,
                'data' => $occasions,
            ]);
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::error('StorefrontProductController@getOccasions failed: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'data' => [],
            ]);
        }
    }
}
