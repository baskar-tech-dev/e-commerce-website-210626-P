<?php

namespace App\Http\Controllers\Api\v1\Admin;

use App\Http\Controllers\Controller;
use App\Models\Size;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class SizeController extends Controller
{
    /**
     * Display a listing of sizes.
     */
    public function index(Request $request): JsonResponse
    {
        $query = Size::with('sizeGroup');

        if ($request->filled('size_group_id')) {
            $query->where('size_group_id', $request->query('size_group_id'));
        }

        if ($request->filled('search')) {
            $search = $request->query('search');
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('code', 'like', "%{$search}%")
                  ->orWhere('measurement_hint', 'like', "%{$search}%");
            });
        }

        if ($request->has('is_active')) {
            $query->where('is_active', filter_var($request->query('is_active'), FILTER_VALIDATE_BOOLEAN));
        }

        $sizes = $query->orderBy('sort_order')->orderBy('name')->get();

        return response()->json([
            'success' => true,
            'data' => $sizes,
        ]);
    }

    /**
     * Store a newly created size.
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'size_group_id' => 'required|exists:size_groups,id',
            'name' => 'required|string|max:50',
            'code' => 'nullable|string|max:50',
            'measurement_hint' => 'nullable|string|max:150',
            'is_active' => 'nullable|boolean',
            'sort_order' => 'nullable|integer',
        ]);

        $size = Size::create([
            'size_group_id' => $validated['size_group_id'],
            'name' => trim($validated['name']),
            'code' => !empty($validated['code']) ? trim($validated['code']) : trim($validated['name']),
            'measurement_hint' => $validated['measurement_hint'] ?? null,
            'is_active' => $validated['is_active'] ?? true,
            'sort_order' => $validated['sort_order'] ?? (Size::where('size_group_id', $validated['size_group_id'])->max('sort_order') + 1),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Size created successfully.',
            'data' => $size->load('sizeGroup'),
        ], 201);
    }

    /**
     * Display the specified size.
     */
    public function show(Size $size): JsonResponse
    {
        return response()->json([
            'success' => true,
            'data' => $size->load('sizeGroup'),
        ]);
    }

    /**
     * Update the specified size.
     */
    public function update(Request $request, Size $size): JsonResponse
    {
        $validated = $request->validate([
            'size_group_id' => 'required|exists:size_groups,id',
            'name' => 'required|string|max:50',
            'code' => 'nullable|string|max:50',
            'measurement_hint' => 'nullable|string|max:150',
            'is_active' => 'nullable|boolean',
            'sort_order' => 'nullable|integer',
        ]);

        $size->update([
            'size_group_id' => $validated['size_group_id'],
            'name' => trim($validated['name']),
            'code' => !empty($validated['code']) ? trim($validated['code']) : $size->code,
            'measurement_hint' => $validated['measurement_hint'] ?? $size->measurement_hint,
            'is_active' => $validated['is_active'] ?? $size->is_active,
            'sort_order' => $validated['sort_order'] ?? $size->sort_order,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Size updated successfully.',
            'data' => $size->load('sizeGroup'),
        ]);
    }

    /**
     * Remove the specified size.
     */
    public function destroy(Size $size): JsonResponse
    {
        $size->delete();

        return response()->json([
            'success' => true,
            'message' => 'Size deleted successfully.',
        ]);
    }
}
