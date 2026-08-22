<?php

namespace App\Http\Controllers\Api\v1\Admin;

use App\Http\Controllers\Controller;
use App\Models\SizeGroup;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Str;

class SizeGroupController extends Controller
{
    /**
     * Display a listing of size groups with their sizes.
     */
    public function index(Request $request): JsonResponse
    {
        $query = SizeGroup::with('sizes');

        if ($request->filled('search')) {
            $search = $request->query('search');
            $query->where('name', 'like', "%{$search}%")
                  ->orWhere('code', 'like', "%{$search}%");
        }

        if ($request->has('is_active')) {
            $query->where('is_active', filter_var($request->query('is_active'), FILTER_VALIDATE_BOOLEAN));
        }

        $sizeGroups = $query->orderBy('sort_order')->orderBy('name')->get();

        return response()->json([
            'success' => true,
            'data' => $sizeGroups,
        ]);
    }

    /**
     * Fast active size groups with active sizes for product forms.
     */
    public function activeList(): JsonResponse
    {
        $sizeGroups = SizeGroup::where('is_active', true)
            ->with(['activeSizes'])
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $sizeGroups,
        ]);
    }

    /**
     * Store a newly created size group.
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:100|unique:size_groups,name',
            'code' => 'nullable|string|max:50|unique:size_groups,code',
            'description' => 'nullable|string|max:255',
            'is_active' => 'nullable|boolean',
            'sort_order' => 'nullable|integer',
        ]);

        $code = !empty($validated['code']) 
            ? strtoupper(Str::slug($validated['code'], '_')) 
            : strtoupper(Str::slug($validated['name'], '_'));

        $sizeGroup = SizeGroup::create([
            'name' => trim($validated['name']),
            'code' => $code,
            'description' => $validated['description'] ?? null,
            'is_active' => $validated['is_active'] ?? true,
            'sort_order' => $validated['sort_order'] ?? (SizeGroup::max('sort_order') + 1),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Size Group created successfully.',
            'data' => $sizeGroup->load('sizes'),
        ], 201);
    }

    /**
     * Display the specified size group.
     */
    public function show(SizeGroup $sizeGroup): JsonResponse
    {
        return response()->json([
            'success' => true,
            'data' => $sizeGroup->load('sizes'),
        ]);
    }

    /**
     * Update the specified size group.
     */
    public function update(Request $request, SizeGroup $sizeGroup): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:100|unique:size_groups,name,' . $sizeGroup->id,
            'code' => 'nullable|string|max:50|unique:size_groups,code,' . $sizeGroup->id,
            'description' => 'nullable|string|max:255',
            'is_active' => 'nullable|boolean',
            'sort_order' => 'nullable|integer',
        ]);

        $code = !empty($validated['code']) 
            ? strtoupper(Str::slug($validated['code'], '_')) 
            : $sizeGroup->code;

        $sizeGroup->update([
            'name' => trim($validated['name']),
            'code' => $code,
            'description' => $validated['description'] ?? $sizeGroup->description,
            'is_active' => $validated['is_active'] ?? $sizeGroup->is_active,
            'sort_order' => $validated['sort_order'] ?? $sizeGroup->sort_order,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Size Group updated successfully.',
            'data' => $sizeGroup->load('sizes'),
        ]);
    }

    /**
     * Remove the specified size group.
     */
    public function destroy(SizeGroup $sizeGroup): JsonResponse
    {
        $sizeGroup->delete();

        return response()->json([
            'success' => true,
            'message' => 'Size Group deleted successfully.',
        ]);
    }
}
