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



        // Price Filter
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


}
