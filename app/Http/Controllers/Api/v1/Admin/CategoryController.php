<?php

namespace App\Http\Controllers\Api\v1\Admin;

use App\Http\Controllers\Controller;
use App\Http\Resources\CategoryResource;
use App\Services\CategoryService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class CategoryController extends Controller
{
    protected $categoryService;

    public function __construct(CategoryService $categoryService)
    {
        $this->categoryService = $categoryService;
    }

    public function index(Request $request): JsonResponse
    {
        try {
            $categories = $request->has('tree') 
                ? $this->categoryService->getCategoryTree() 
                : $this->categoryService->getAllCategories();

            return response()->json([
                'success' => true,
                'message' => 'Categories retrieved successfully',
                'data' => CategoryResource::collection($categories)
            ]);
        } catch (\Exception $e) {
            Log::error('CategoryController@index failed: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve categories',
                'error_code' => 'SERVER_ERROR'
            ], 500);
        }
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'parent_id' => 'nullable|exists:categories,id',
            'name' => 'required|string|max:150',
            'slug' => 'nullable|string|max:180',
            'description' => 'nullable|string',
            'image' => 'nullable|string|max:500',
            'icon' => 'nullable|string|max:50',
            'meta_title' => 'nullable|string|max:200',
            'meta_description' => 'nullable|string|max:500',
            'sort_order' => 'nullable|integer',
            'is_active' => 'nullable|boolean',
            'is_featured' => 'nullable|boolean',
        ]);

        try {
            $category = $this->categoryService->createCategory($validated);

            return response()->json([
                'success' => true,
                'message' => 'Category created successfully',
                'data' => new CategoryResource($category)
            ], 201);
        } catch (\Exception $e) {
            Log::error('CategoryController@store failed: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to create category',
                'error_code' => 'SERVER_ERROR'
            ], 500);
        }
    }

    public function show(int $id): JsonResponse
    {
        $category = $this->categoryService->getCategoryById($id);

        if (!$category) {
            return response()->json([
                'success' => false,
                'message' => 'Category not found',
                'error_code' => 'NOT_FOUND'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'message' => 'Category retrieved successfully',
            'data' => new CategoryResource($category)
        ]);
    }

    public function update(Request $request, int $id): JsonResponse
    {
        $validated = $request->validate([
            'parent_id' => 'nullable|exists:categories,id|different:id',
            'name' => 'sometimes|required|string|max:150',
            'slug' => 'nullable|string|max:180',
            'description' => 'nullable|string',
            'image' => 'nullable|string|max:500',
            'icon' => 'nullable|string|max:50',
            'meta_title' => 'nullable|string|max:200',
            'meta_description' => 'nullable|string|max:500',
            'sort_order' => 'nullable|integer',
            'is_active' => 'nullable|boolean',
            'is_featured' => 'nullable|boolean',
        ]);

        try {
            $category = $this->categoryService->updateCategory($id, $validated);

            if (!$category) {
                return response()->json([
                    'success' => false,
                    'message' => 'Category not found',
                    'error_code' => 'NOT_FOUND'
                ], 404);
            }

            return response()->json([
                'success' => true,
                'message' => 'Category updated successfully',
                'data' => new CategoryResource($category)
            ]);
        } catch (\Exception $e) {
            Log::error('CategoryController@update failed: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to update category',
                'error_code' => 'SERVER_ERROR'
            ], 500);
        }
    }

    public function destroy(int $id): JsonResponse
    {
        try {
            $deleted = $this->categoryService->deleteCategory($id);

            if (!$deleted) {
                return response()->json([
                    'success' => false,
                    'message' => 'Category not found',
                    'error_code' => 'NOT_FOUND'
                ], 404);
            }

            return response()->json([
                'success' => true,
                'message' => 'Category deleted successfully'
            ]);
        } catch (\Exception $e) {
            if ($e->getCode() === 409) {
                return response()->json([
                    'success' => false,
                    'message' => $e->getMessage(),
                    'error_code' => 'CONFLICT'
                ], 409);
            }

            Log::error('CategoryController@destroy failed: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete category',
                'error_code' => 'SERVER_ERROR'
            ], 500);
        }
    }
}
