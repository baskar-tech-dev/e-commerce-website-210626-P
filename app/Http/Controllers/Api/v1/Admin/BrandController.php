<?php

namespace App\Http\Controllers\Api\v1\Admin;

use App\Http\Controllers\Controller;
use App\Http\Resources\BrandResource;
use App\Services\BrandService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class BrandController extends Controller
{
    protected $brandService;

    public function __construct(BrandService $brandService)
    {
        $this->brandService = $brandService;
    }

    public function index(): JsonResponse
    {
        try {
            $brands = $this->brandService->getAllBrands();

            return response()->json([
                'success' => true,
                'message' => 'Brands retrieved successfully',
                'data' => BrandResource::collection($brands)
            ]);
        } catch (\Exception $e) {
            Log::error('BrandController@index failed: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve brands',
                'error_code' => 'SERVER_ERROR'
            ], 500);
        }
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:150',
            'slug' => 'nullable|string|max:180',
            'description' => 'nullable|string',
            'logo' => 'nullable|string|max:500',
            'website' => 'nullable|string|max:500',
            'meta_title' => 'nullable|string|max:200',
            'meta_description' => 'nullable|string|max:500',
            'sort_order' => 'nullable|integer',
            'is_active' => 'nullable|boolean',
        ]);

        try {
            $brand = $this->brandService->createBrand($validated);

            return response()->json([
                'success' => true,
                'message' => 'Brand created successfully',
                'data' => new BrandResource($brand)
            ], 201);
        } catch (\Exception $e) {
            Log::error('BrandController@store failed: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to create brand',
                'error_code' => 'SERVER_ERROR'
            ], 500);
        }
    }

    public function show(int $id): JsonResponse
    {
        $brand = $this->brandService->getBrandById($id);

        if (!$brand) {
            return response()->json([
                'success' => false,
                'message' => 'Brand not found',
                'error_code' => 'NOT_FOUND'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'message' => 'Brand retrieved successfully',
            'data' => new BrandResource($brand)
        ]);
    }

    public function update(Request $request, int $id): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'sometimes|required|string|max:150',
            'slug' => 'nullable|string|max:180',
            'description' => 'nullable|string',
            'logo' => 'nullable|string|max:500',
            'website' => 'nullable|string|max:500',
            'meta_title' => 'nullable|string|max:200',
            'meta_description' => 'nullable|string|max:500',
            'sort_order' => 'nullable|integer',
            'is_active' => 'nullable|boolean',
        ]);

        try {
            $brand = $this->brandService->updateBrand($id, $validated);

            if (!$brand) {
                return response()->json([
                    'success' => false,
                    'message' => 'Brand not found',
                    'error_code' => 'NOT_FOUND'
                ], 404);
            }

            return response()->json([
                'success' => true,
                'message' => 'Brand updated successfully',
                'data' => new BrandResource($brand)
            ]);
        } catch (\Exception $e) {
            Log::error('BrandController@update failed: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to update brand',
                'error_code' => 'SERVER_ERROR'
            ], 500);
        }
    }

    public function destroy(int $id): JsonResponse
    {
        try {
            $deleted = $this->brandService->deleteBrand($id);

            if (!$deleted) {
                return response()->json([
                    'success' => false,
                    'message' => 'Brand not found',
                    'error_code' => 'NOT_FOUND'
                ], 404);
            }

            return response()->json([
                'success' => true,
                'message' => 'Brand deleted successfully'
            ]);
        } catch (\Exception $e) {
            Log::error('BrandController@destroy failed: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete brand',
                'error_code' => 'SERVER_ERROR'
            ], 500);
        }
    }
}
