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

        // Category Filter (ID or Slug)
        if ($request->has('category_id') && !empty($request->input('category_id'))) {
            $categoryId = $request->input('category_id');
            $categoryIds = Category::where('parent_id', $categoryId)
                ->pluck('id')
                ->push((int)$categoryId)
                ->toArray();
            $query->whereIn('category_id', $categoryIds);
        } elseif ($request->has('category') && !empty($request->input('category'))) {
            $catSlug = strtolower($request->input('category'));
            if ($catSlug === 'sarees' || $catSlug === 'blouses' || $catSlug === 'kurtis' || $catSlug === 'dupatta') {
                $category = Category::where('slug', $catSlug)->first();
                if ($category) {
                    $query->where('category_id', $category->id);
                }
        // Featured Filter
        if ($request->boolean('featured') || $request->input('featured') === '1' || $request->input('is_featured') === '1') {
            $query->where('is_featured', true);
        }

        // Occasion Filter
        if ($request->filled('occasion')) {
            $occ = strtolower($request->input('occasion'));
            $query->where(function ($q) use ($occ) {
                $q->where('name', 'like', "%{$occ}%")
                  ->orWhere('description', 'like', "%{$occ}%");
            });
        }

        // Collection Filter
        if ($request->filled('collection')) {
            $col = strtolower($request->input('collection'));
            if ($col === 'new_arrival' || $col === 'new') {
                $query->orderBy('created_at', 'desc');
            } elseif ($col === 'trending') {
                $query->where('is_featured', true);
            } elseif ($col === 'premium' || $col === 'luxury') {
                $query->where('selling_price', '>=', 1000);
            }
        }

        // Price Range Preset Filter (Under 499, Under 999, Luxury)
        if ($request->filled('price_range')) {
            $range = $request->input('price_range');
            if ($range === 'under_499' || $range === '499') {
                $query->where('selling_price', '<=', 499);
            } elseif ($range === 'under_999' || $range === '999') {
                $query->where('selling_price', '<=', 999);
            } elseif ($range === 'luxury' || $range === '1500+') {
                $query->where('selling_price', '>=', 1500);
            }
        }

        // Price Limit Filter (Custom Min/Max)
        if ($request->filled('min_price')) {
            $query->where('selling_price', '>=', (float) $request->input('min_price'));
        }
        if ($request->filled('max_price')) {
            $query->where('selling_price', '<=', (float) $request->input('max_price'));
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
        } else {
            $product = $query->find((int)$id);
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

        // Related Products (products in same category)
        $related = Product::with('images')
            ->where('category_id', $product->category_id)
            ->where('id', '!=', $product->id)
            ->where('is_active', true)
            ->limit(8)
            ->get();

        return response()->json([
            'success' => true,
            'data' => $product,
            'related' => $related,
        ]);
    }

    /**
     * Public categories list for storefront filter dropdowns and homepage category grid.
     */
    public function categories(Request $request): JsonResponse
    {
        $query = Category::where('is_active', true);

        if ($request->boolean('featured') || $request->boolean('home') || $request->input('is_featured') === '1') {
            $query->where('is_featured', true);
        } elseif ($request->boolean('all')) {
            // Return all active categories including subcategories
        } else {
            $query->whereNull('parent_id');
        }

        $categories = $query->with(['children' => function($subQuery) {
                $subQuery->where('is_active', true)->orderBy('sort_order')->orderBy('name');
            }])
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get(['id', 'parent_id', 'name', 'slug', 'icon', 'image', 'description', 'sort_order', 'is_active', 'is_featured']);

        return response()->json([
            'success' => true,
            'data' => $categories,
        ]);
    }


}
