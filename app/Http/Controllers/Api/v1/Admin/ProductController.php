<?php

namespace App\Http\Controllers\Api\v1\Admin;

use App\Http\Controllers\Controller;
use App\Http\Resources\ProductResource;
use App\Services\ProductService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;

class ProductController extends Controller
{
    protected $productService;

    public function __construct(ProductService $productService)
    {
        $this->productService = $productService;
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $filters = $request->only([
                'category_id',
                'is_active',
                'search',
                'min_price',
                'max_price',
                'sort_by',
            ]);

            $perPage = $request->input('per_page', 15);
            $products = $this->productService->getPaginatedProducts($filters, $perPage);

            return response()->json([
                'success' => true,
                'message' => 'Products retrieved successfully',
                'data' => ProductResource::collection($products),
                'meta' => [
                    'current_page' => $products->currentPage(),
                    'last_page' => $products->lastPage(),
                    'per_page' => $products->perPage(),
                    'total' => $products->total(),
                ]
            ]);
        } catch (\Exception $e) {
            Log::error('ProductController@index failed: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve products',
                'error_code' => 'SERVER_ERROR'
            ], 500);
        }
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'category_id' => 'required|exists:categories,id',
            'name' => 'required|string|max:300',
            'slug' => 'nullable|string|max:350',
            'short_description' => 'nullable|string|max:500',
            'description' => 'nullable|string',
            'material' => 'nullable|string|max:200',
            'care_instructions' => 'nullable|string',
            
            'mrp' => 'required|numeric|min:0',
            'selling_price' => 'required|numeric|min:0|lte:mrp',
            'cost_price' => 'nullable|numeric|min:0',
            
            'tax_category' => 'nullable|string|max:50',
            'gst_rate' => 'nullable|numeric|min:0|max:100',
            'hsn_code' => 'nullable|string|max:20',
            'weight' => 'nullable|numeric|min:0',
            
            'is_active' => 'nullable|boolean',
            'is_featured' => 'nullable|boolean',
            'is_new_arrival' => 'nullable|boolean',
            'is_bestseller' => 'nullable|boolean',
            'is_returnable' => 'nullable|boolean',
            'return_window_days' => 'nullable|integer|min:0',
            
            'meta_title' => 'nullable|string|max:200',
            'meta_description' => 'nullable|string|max:500',
            'meta_keywords' => 'nullable|string|max:300',
            
            'tag_ids' => 'nullable|array',
            'tag_ids.*' => 'exists:tags,id',
            
            // Nested variants
            'variants' => 'nullable|array',
            'variants.*.sku' => 'required|string|max:100',
            'variants.*.size' => 'nullable|string|max:30',
            'variants.*.color' => 'nullable|string|max:50',
            'variants.*.color_code' => 'nullable|string|max:7',
            'variants.*.mrp' => 'nullable|numeric|min:0',
            'variants.*.selling_price' => 'nullable|numeric|min:0|lte:variants.*.mrp',
            'variants.*.cost_price' => 'nullable|numeric|min:0',
            'variants.*.stock_quantity' => 'nullable|integer|min:0',
            'variants.*.low_stock_threshold' => 'nullable|integer|min:0',
            'variants.*.weight' => 'nullable|numeric|min:0',
            'variants.*.barcode' => 'nullable|string|max:50',
            'variants.*.is_active' => 'nullable|boolean',
            'variants.*.sort_order' => 'nullable|integer',
            
            // Nested images
            'images' => 'nullable|array',
            'images.*.url' => 'required|string|max:500',
            'images.*.thumbnail_url' => 'nullable|string|max:500',
            'images.*.alt_text' => 'nullable|string|max:200',
            'images.*.sort_order' => 'nullable|integer',
            'images.*.is_primary' => 'nullable|boolean',
            'images.*.variant_sku' => 'nullable|string|max:100',
            'images.*.temp_path' => 'nullable|array',
            'images.*.temp_path.original' => 'nullable|string',
            'images.*.temp_path.webp' => 'nullable|string',
            'images.*.temp_path.thumb' => 'nullable|string',
            'images.*.temp_path.hash' => 'nullable|string',
            'images.*.temp_path.width' => 'nullable|integer',
            'images.*.temp_path.height' => 'nullable|integer',
            'images.*.temp_path.file_size' => 'nullable|integer',
        ]);

        try {
            $product = $this->productService->createProduct($validated);

            return response()->json([
                'success' => true,
                'message' => 'Product created successfully',
                'data' => new ProductResource($product)
            ], 201);
        } catch (ValidationException $e) {
            throw $e;
        } catch (\Exception $e) {
            Log::error('ProductController@store failed: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to create product: ' . $e->getMessage(),
                'error_code' => 'SERVER_ERROR'
            ], 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(int $id): JsonResponse
    {
        try {
            $product = $this->productService->getProductById($id);

            if (!$product) {
                return response()->json([
                    'success' => false,
                    'message' => 'Product not found',
                    'error_code' => 'NOT_FOUND'
                ], 404);
            }

            return response()->json([
                'success' => true,
                'message' => 'Product retrieved successfully',
                'data' => new ProductResource($product)
            ]);
        } catch (\Exception $e) {
            Log::error('ProductController@show failed: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve product',
                'error_code' => 'SERVER_ERROR'
            ], 500);
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, int $id): JsonResponse
    {
        $validated = $request->validate([
            'category_id' => 'sometimes|required|exists:categories,id',
            'name' => 'sometimes|required|string|max:300',
            'slug' => 'nullable|string|max:350',
            'short_description' => 'nullable|string|max:500',
            'description' => 'nullable|string',
            'material' => 'nullable|string|max:200',
            'care_instructions' => 'nullable|string',
            
            'mrp' => 'sometimes|required|numeric|min:0',
            'selling_price' => 'sometimes|required|numeric|min:0|lte:mrp',
            'cost_price' => 'nullable|numeric|min:0',
            
            'tax_category' => 'nullable|string|max:50',
            'gst_rate' => 'nullable|numeric|min:0|max:100',
            'hsn_code' => 'nullable|string|max:20',
            'weight' => 'nullable|numeric|min:0',
            
            'is_active' => 'nullable|boolean',
            'is_featured' => 'nullable|boolean',
            'is_new_arrival' => 'nullable|boolean',
            'is_bestseller' => 'nullable|boolean',
            'is_returnable' => 'nullable|boolean',
            'return_window_days' => 'nullable|integer|min:0',
            
            'meta_title' => 'nullable|string|max:200',
            'meta_description' => 'nullable|string|max:500',
            'meta_keywords' => 'nullable|string|max:300',
            
            'tag_ids' => 'nullable|array',
            'tag_ids.*' => 'exists:tags,id',
            
            // Nested variants
            'variants' => 'nullable|array',
            'variants.*.id' => 'nullable|integer|exists:product_variants,id',
            'variants.*.sku' => 'required|string|max:100',
            'variants.*.size' => 'nullable|string|max:30',
            'variants.*.color' => 'nullable|string|max:50',
            'variants.*.color_code' => 'nullable|string|max:7',
            'variants.*.mrp' => 'nullable|numeric|min:0',
            'variants.*.selling_price' => 'nullable|numeric|min:0|lte:variants.*.mrp',
            'variants.*.cost_price' => 'nullable|numeric|min:0',
            'variants.*.stock_quantity' => 'nullable|integer|min:0',
            'variants.*.low_stock_threshold' => 'nullable|integer|min:0',
            'variants.*.weight' => 'nullable|numeric|min:0',
            'variants.*.barcode' => 'nullable|string|max:50',
            'variants.*.is_active' => 'nullable|boolean',
            'variants.*.sort_order' => 'nullable|integer',
            
            // Nested images
            'images' => 'nullable|array',
            'images.*.id' => 'nullable|integer|exists:product_images,id',
            'images.*.url' => 'required|string|max:500',
            'images.*.thumbnail_url' => 'nullable|string|max:500',
            'images.*.alt_text' => 'nullable|string|max:200',
            'images.*.sort_order' => 'nullable|integer',
            'images.*.is_primary' => 'nullable|boolean',
            'images.*.variant_sku' => 'nullable|string|max:100',
            'images.*.temp_path' => 'nullable|array',
            'images.*.temp_path.original' => 'nullable|string',
            'images.*.temp_path.webp' => 'nullable|string',
            'images.*.temp_path.thumb' => 'nullable|string',
            'images.*.temp_path.hash' => 'nullable|string',
            'images.*.temp_path.width' => 'nullable|integer',
            'images.*.temp_path.height' => 'nullable|integer',
            'images.*.temp_path.file_size' => 'nullable|integer',
        ]);

        try {
            $product = $this->productService->updateProduct($id, $validated);

            if (!$product) {
                return response()->json([
                    'success' => false,
                    'message' => 'Product not found',
                    'error_code' => 'NOT_FOUND'
                ], 404);
            }

            return response()->json([
                'success' => true,
                'message' => 'Product updated successfully',
                'data' => new ProductResource($product)
            ]);
        } catch (ValidationException $e) {
            throw $e;
        } catch (\Exception $e) {
            Log::error('ProductController@update failed: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to update product: ' . $e->getMessage(),
                'error_code' => 'SERVER_ERROR'
            ], 500);
        }
    }

    public function destroy(int $id): JsonResponse
    {
        try {
            $deleted = $this->productService->deleteProduct($id);

            if (!$deleted) {
                return response()->json([
                    'success' => false,
                    'message' => 'Product not found',
                    'error_code' => 'NOT_FOUND'
                ], 404);
            }

            return response()->json([
                'success' => true,
                'message' => 'Product deleted successfully'
            ]);
        } catch (\Exception $e) {
            Log::error('ProductController@destroy failed: ' . $e->getMessage());
            
            if ($e->getCode() === 409) {
                return response()->json([
                    'success' => false,
                    'error_code' => 'PRODUCT_USED',
                    'message' => $e->getMessage()
                ], 409);
            }

            return response()->json([
                'success' => false,
                'message' => 'Failed to delete product',
                'error_code' => 'SERVER_ERROR'
            ], 500);
        }
    }
}
