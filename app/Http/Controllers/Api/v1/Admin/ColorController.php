<?php

namespace App\Http\Controllers\Api\v1\Admin;

use App\Http\Controllers\Controller;
use App\Models\Color;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class ColorController extends Controller
{
    /**
     * Display a listing of colors.
     */
    public function index(Request $request): JsonResponse
    {
        $query = Color::query();

        if ($request->filled('search')) {
            $search = $request->query('search');
            $query->where('name', 'like', "%{$search}%")
                  ->orWhere('code', 'like', "%{$search}%");
        }

        if ($request->has('is_active')) {
            $query->where('is_active', filter_var($request->query('is_active'), FILTER_VALIDATE_BOOLEAN));
        }

        $colors = $query->orderBy('sort_order')->orderBy('name')->get();

        return response()->json([
            'success' => true,
            'data' => $colors,
        ]);
    }

    /**
     * Fast active colors list for product forms and storefronts.
     */
    public function activeList(): JsonResponse
    {
        $colors = Color::where('is_active', true)
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $colors,
        ]);
    }

    /**
     * Store a newly created color.
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:100|unique:colors,name',
            'code' => 'required|string|max:20',
            'is_active' => 'nullable|boolean',
            'sort_order' => 'nullable|integer',
        ]);

        $color = Color::create([
            'name' => trim($validated['name']),
            'code' => trim($validated['code']),
            'is_active' => $validated['is_active'] ?? true,
            'sort_order' => $validated['sort_order'] ?? (Color::max('sort_order') + 1),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Color created successfully.',
            'data' => $color,
        ], 201);
    }

    /**
     * Display the specified color.
     */
    public function show(Color $color): JsonResponse
    {
        return response()->json([
            'success' => true,
            'data' => $color,
        ]);
    }

    /**
     * Update the specified color.
     */
    public function update(Request $request, Color $color): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:100|unique:colors,name,' . $color->id,
            'code' => 'required|string|max:20',
            'is_active' => 'nullable|boolean',
            'sort_order' => 'nullable|integer',
        ]);

        $color->update([
            'name' => trim($validated['name']),
            'code' => trim($validated['code']),
            'is_active' => $validated['is_active'] ?? $color->is_active,
            'sort_order' => $validated['sort_order'] ?? $color->sort_order,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Color updated successfully.',
            'data' => $color,
        ]);
    }

    /**
     * Remove the specified color.
     */
    public function destroy(Color $color): JsonResponse
    {
        $color->delete();

        return response()->json([
            'success' => true,
            'message' => 'Color deleted successfully.',
        ]);
    }
}
