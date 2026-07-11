<?php

namespace App\Http\Controllers\Api\v1\Admin;

use App\Http\Controllers\Controller;
use App\Models\Coupon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Exception;

class CouponController extends Controller
{
    /**
     * Display a listing of coupons.
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $query = Coupon::query();

            if ($request->filled('search')) {
                $search = $request->input('search');
                $query->where(function ($q) use ($search) {
                    $q->where('code', 'like', "%{$search}%")
                      ->orWhere('name', 'like', "%{$search}%");
                });
            }

            if ($request->filled('status')) {
                $status = $request->input('status');
                if ($status === 'active') {
                    $query->where('is_active', true)
                          ->where('starts_at', '<=', now())
                          ->where(function ($q) {
                              $q->whereNull('expires_at')
                                ->orWhere('expires_at', '>=', now());
                          });
                } elseif ($status === 'inactive') {
                    $query->where(function ($q) {
                        $q->where('is_active', false)
                          ->orWhere('starts_at', '>', now())
                          ->orWhere('expires_at', '<', now());
                    });
                }
            }

            $perPage = $request->input('per_page', 15);
            $coupons = $query->orderBy('created_at', 'desc')->paginate($perPage);

            return response()->json([
                'success' => true,
                'message' => 'Coupons retrieved successfully',
                'data' => $coupons->items(),
                'meta' => [
                    'current_page' => $coupons->currentPage(),
                    'last_page' => $coupons->lastPage(),
                    'per_page' => $coupons->perPage(),
                    'total' => $coupons->total(),
                ]
            ]);
        } catch (Exception $e) {
            Log::error('CouponController@index failed: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve coupons',
                'error_code' => 'SERVER_ERROR'
            ], 500);
        }
    }

    /**
     * Store a newly created coupon.
     */
    public function store(Request $request): JsonResponse
    {
        if ($request->has('code')) {
            $request->merge(['code' => strtoupper($request->input('code'))]);
        }

        $validated = $request->validate([
            'code' => 'required|string|max:50|unique:coupons,code',
            'name' => 'required|string|max:200',
            'description' => 'nullable|string|max:500',
            'type' => 'required|string|in:percentage,flat,free_shipping',
            'value' => 'required|numeric|min:0',
            'max_discount' => 'nullable|numeric|min:0',
            'min_order_value' => 'nullable|numeric|min:0',
            'max_uses_total' => 'nullable|integer|min:1',
            'max_uses_per_user' => 'required|integer|min:1',
            'starts_at' => 'required|date',
            'expires_at' => 'nullable|date|after:starts_at',
            'is_active' => 'required|boolean',
            'applicable_category_ids' => 'nullable|array',
            'applicable_brand_ids' => 'nullable|array',
            'applicable_product_ids' => 'nullable|array',
            'excluded_product_ids' => 'nullable|array',
            'first_order_only' => 'required|boolean',
            'is_auto_apply' => 'required|boolean',
            'is_combinable' => 'required|boolean',
        ]);

        try {
            $validated['created_by'] = auth()->id();
            
            // Uppercase the code
            $validated['code'] = strtoupper($validated['code']);

            $coupon = Coupon::create($validated);

            return response()->json([
                'success' => true,
                'message' => 'Coupon created successfully',
                'data' => $coupon
            ], 201);
        } catch (Exception $e) {
            Log::error('CouponController@store failed: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to create coupon: ' . $e->getMessage(),
                'error_code' => 'SERVER_ERROR'
            ], 500);
        }
    }

    /**
     * Display detailed coupon view.
     */
    public function show(int $id): JsonResponse
    {
        try {
            $coupon = Coupon::find($id);

            if (!$coupon) {
                return response()->json([
                    'success' => false,
                    'message' => 'Coupon not found',
                    'error_code' => 'NOT_FOUND'
                ], 404);
            }

            return response()->json([
                'success' => true,
                'message' => 'Coupon details retrieved successfully',
                'data' => $coupon
            ]);
        } catch (Exception $e) {
            Log::error('CouponController@show failed: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve coupon details',
                'error_code' => 'SERVER_ERROR'
            ], 500);
        }
    }

    /**
     * Update specified coupon.
     */
    public function update(Request $request, int $id): JsonResponse
    {
        $coupon = Coupon::find($id);

        if (!$coupon) {
            return response()->json([
                'success' => false,
                'message' => 'Coupon not found',
                'error_code' => 'NOT_FOUND'
            ], 404);
        }

        if ($request->has('code')) {
            $request->merge(['code' => strtoupper($request->input('code'))]);
        }

        $validated = $request->validate([
            'code' => 'required|string|max:50|unique:coupons,code,' . $id,
            'name' => 'required|string|max:200',
            'description' => 'nullable|string|max:500',
            'type' => 'required|string|in:percentage,flat,free_shipping',
            'value' => 'required|numeric|min:0',
            'max_discount' => 'nullable|numeric|min:0',
            'min_order_value' => 'nullable|numeric|min:0',
            'max_uses_total' => 'nullable|integer|min:1',
            'max_uses_per_user' => 'required|integer|min:1',
            'starts_at' => 'required|date',
            'expires_at' => 'nullable|date|after:starts_at',
            'is_active' => 'required|boolean',
            'applicable_category_ids' => 'nullable|array',
            'applicable_brand_ids' => 'nullable|array',
            'applicable_product_ids' => 'nullable|array',
            'excluded_product_ids' => 'nullable|array',
            'first_order_only' => 'required|boolean',
            'is_auto_apply' => 'required|boolean',
            'is_combinable' => 'required|boolean',
        ]);

        try {
            // Uppercase the code
            $validated['code'] = strtoupper($validated['code']);

            $coupon->update($validated);

            return response()->json([
                'success' => true,
                'message' => 'Coupon updated successfully',
                'data' => $coupon
            ]);
        } catch (Exception $e) {
            Log::error('CouponController@update failed: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to update coupon: ' . $e->getMessage(),
                'error_code' => 'SERVER_ERROR'
            ], 500);
        }
    }

    /**
     * Remove specified coupon.
     */
    public function destroy(int $id): JsonResponse
    {
        try {
            $coupon = Coupon::find($id);

            if (!$coupon) {
                return response()->json([
                    'success' => false,
                    'message' => 'Coupon not found',
                    'error_code' => 'NOT_FOUND'
                ], 404);
            }

            $coupon->delete();

            return response()->json([
                'success' => true,
                'message' => 'Coupon deleted successfully'
            ]);
        } catch (Exception $e) {
            Log::error('CouponController@destroy failed: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete coupon',
                'error_code' => 'SERVER_ERROR'
            ], 500);
        }
    }
}
