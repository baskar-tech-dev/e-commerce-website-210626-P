<?php

namespace App\Http\Controllers\Api\v1\Admin;

use App\Http\Controllers\Controller;
use App\Models\Courier;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class CourierController extends Controller
{
    /**
     * Display a listing of courier partners.
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $query = Courier::query();

            // Keyword search
            if ($request->filled('search')) {
                $search = $request->input('search');
                $query->where(function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                      ->orWhere('code', 'like', "%{$search}%")
                      ->orWhere('contact_person', 'like', "%{$search}%")
                      ->orWhere('contact_number', 'like', "%{$search}%")
                      ->orWhere('contact_email', 'like', "%{$search}%");
                });
            }

            // Status filter
            if ($request->has('is_active') && $request->input('is_active') !== '') {
                $query->where('is_active', filter_var($request->input('is_active'), FILTER_VALIDATE_BOOLEAN));
            }

            // Return all active or paginated
            if ($request->boolean('all')) {
                $couriers = $query->orderBy('sort_order')->orderBy('name')->get();
                return response()->json([
                    'success' => true,
                    'data' => $couriers,
                ]);
            }

            $perPage = (int) $request->input('per_page', 15);
            $couriers = $query->withCount('orders')
                ->orderBy('sort_order')
                ->orderBy('name')
                ->paginate($perPage);

            return response()->json([
                'success' => true,
                'data' => $couriers->items(),
                'meta' => [
                    'current_page' => $couriers->currentPage(),
                    'last_page' => $couriers->lastPage(),
                    'per_page' => $couriers->perPage(),
                    'total' => $couriers->total(),
                ],
            ]);
        } catch (Exception $e) {
            Log::error('CourierController@index error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to load couriers',
            ], 500);
        }
    }

    /**
     * Get active couriers list for dropdown selection in orders.
     */
    public function activeList(): JsonResponse
    {
        try {
            $couriers = Courier::where('is_active', true)
                ->orderBy('sort_order')
                ->orderBy('name')
                ->get(['id', 'name', 'code', 'tracking_page_link', 'contact_person', 'contact_number', 'contact_email']);

            return response()->json([
                'success' => true,
                'data' => $couriers,
            ]);
        } catch (Exception $e) {
            Log::error('CourierController@activeList error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to load active couriers list',
            ], 500);
        }
    }

    /**
     * Store a newly created courier partner.
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:100|unique:couriers,name',
            'code' => 'nullable|string|max:50|unique:couriers,code',
            'tracking_page_link' => 'nullable|string|max:500',
            'contact_person' => 'nullable|string|max:100',
            'contact_number' => 'nullable|string|max:50',
            'contact_email' => 'nullable|email|max:100',
            'is_active' => 'nullable|boolean',
            'sort_order' => 'nullable|integer',
            'notes' => 'nullable|string|max:1000',
        ]);

        if (empty($validated['code'])) {
            $validated['code'] = Str::slug($validated['name']);
        }

        try {
            $courier = Courier::create($validated);

            return response()->json([
                'success' => true,
                'message' => 'Courier partner created successfully',
                'data' => $courier,
            ], 201);
        } catch (Exception $e) {
            Log::error('CourierController@store error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to create courier partner',
            ], 500);
        }
    }

    /**
     * Display the specified courier partner.
     */
    public function show(int $id): JsonResponse
    {
        try {
            $courier = Courier::withCount('orders')->find($id);

            if (!$courier) {
                return response()->json([
                    'success' => false,
                    'message' => 'Courier partner not found',
                ], 404);
            }

            return response()->json([
                'success' => true,
                'data' => $courier,
            ]);
        } catch (Exception $e) {
            Log::error('CourierController@show error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve courier partner',
            ], 500);
        }
    }

    /**
     * Update the specified courier partner.
     */
    public function update(Request $request, int $id): JsonResponse
    {
        $courier = Courier::find($id);

        if (!$courier) {
            return response()->json([
                'success' => false,
                'message' => 'Courier partner not found',
            ], 404);
        }

        $validated = $request->validate([
            'name' => 'required|string|max:100|unique:couriers,name,' . $id,
            'code' => 'nullable|string|max:50|unique:couriers,code,' . $id,
            'tracking_page_link' => 'nullable|string|max:500',
            'contact_person' => 'nullable|string|max:100',
            'contact_number' => 'nullable|string|max:50',
            'contact_email' => 'nullable|email|max:100',
            'is_active' => 'nullable|boolean',
            'sort_order' => 'nullable|integer',
            'notes' => 'nullable|string|max:1000',
        ]);

        try {
            $courier->update($validated);

            return response()->json([
                'success' => true,
                'message' => 'Courier partner updated successfully',
                'data' => $courier,
            ]);
        } catch (Exception $e) {
            Log::error('CourierController@update error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to update courier partner',
            ], 500);
        }
    }

    /**
     * Toggle the active status of a courier partner.
     */
    public function toggleActive(int $id): JsonResponse
    {
        try {
            $courier = Courier::find($id);

            if (!$courier) {
                return response()->json([
                    'success' => false,
                    'message' => 'Courier partner not found',
                ], 404);
            }

            $courier->is_active = !$courier->is_active;
            $courier->save();

            return response()->json([
                'success' => true,
                'message' => 'Courier status updated to ' . ($courier->is_active ? 'Active' : 'Inactive'),
                'data' => $courier,
            ]);
        } catch (Exception $e) {
            Log::error('CourierController@toggleActive error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to toggle courier status',
            ], 500);
        }
    }

    /**
     * Remove the specified courier partner.
     */
    public function destroy(int $id): JsonResponse
    {
        try {
            $courier = Courier::find($id);

            if (!$courier) {
                return response()->json([
                    'success' => false,
                    'message' => 'Courier partner not found',
                ], 404);
            }

            $courier->delete();

            return response()->json([
                'success' => true,
                'message' => 'Courier partner deleted successfully',
            ]);
        } catch (Exception $e) {
            Log::error('CourierController@destroy error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete courier partner',
            ], 500);
        }
    }
}
