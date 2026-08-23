<?php

namespace App\Http\Controllers\Api\v1\Admin;

use App\Http\Controllers\Controller;
use App\Models\Occasion;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class OccasionController extends Controller
{
    /**
     * Display a listing of occasions with metadata.
     */
    public function index(Request $request): JsonResponse
    {
        $status = $request->query('status', 'all');

        $query = Occasion::query()->orderBy('sort_order', 'asc')->orderBy('id', 'asc');

        if ($status === 'active') {
            $query->where('is_active', true);
        } elseif ($status === 'inactive') {
            $query->where('is_active', false);
        }

        $occasions = $query->get();

        $totalCount = Occasion::count();
        $activeCount = Occasion::where('is_active', true)->count();
        $inactiveCount = Occasion::where('is_active', false)->count();

        return response()->json([
            'success' => true,
            'data' => $occasions,
            'meta' => [
                'total' => $totalCount,
                'active_count' => $activeCount,
                'inactive_count' => $inactiveCount,
            ]
        ]);
    }

    /**
     * Store a newly created occasion in storage.
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:100',
            'slug' => 'nullable|string|max:120|unique:occasions,slug',
            'image_url' => 'nullable|string|max:255',
            'subtitle' => 'nullable|string|max:255',
            'sort_order' => 'nullable|integer',
            'is_active' => 'nullable|boolean',
        ]);

        if (empty($validated['slug'])) {
            $slugBase = Str::slug($validated['name']);
            $slug = $slugBase;
            $counter = 1;
            while (Occasion::where('slug', $slug)->exists()) {
                $slug = $slugBase . '-' . $counter;
                $counter++;
            }
            $validated['slug'] = $slug;
        }

        if (!isset($validated['sort_order'])) {
            $maxOrder = Occasion::max('sort_order') ?? 0;
            $validated['sort_order'] = $maxOrder + 1;
        }

        $validated['is_active'] = $validated['is_active'] ?? true;

        $occasion = Occasion::create($validated);

        return response()->json([
            'success' => true,
            'message' => 'Occasion created successfully',
            'data' => $occasion
        ], 201);
    }

    /**
     * Display the specified occasion.
     */
    public function show(int $id): JsonResponse
    {
        $occasion = Occasion::find($id);

        if (!$occasion) {
            return response()->json([
                'success' => false,
                'message' => 'Occasion not found'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $occasion
        ]);
    }

    /**
     * Update the specified occasion in storage.
     */
    public function update(Request $request, int $id): JsonResponse
    {
        $occasion = Occasion::find($id);

        if (!$occasion) {
            return response()->json([
                'success' => false,
                'message' => 'Occasion not found'
            ], 404);
        }

        $validated = $request->validate([
            'name' => 'required|string|max:100',
            'slug' => 'nullable|string|max:120|unique:occasions,slug,' . $id,
            'image_url' => 'nullable|string|max:255',
            'subtitle' => 'nullable|string|max:255',
            'sort_order' => 'nullable|integer',
            'is_active' => 'nullable|boolean',
        ]);

        if (empty($validated['slug'])) {
            $slugBase = Str::slug($validated['name']);
            $slug = $slugBase;
            $counter = 1;
            while (Occasion::where('slug', $slug)->where('id', '!=', $id)->exists()) {
                $slug = $slugBase . '-' . $counter;
                $counter++;
            }
            $validated['slug'] = $slug;
        }

        $occasion->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'Occasion updated successfully',
            'data' => $occasion
        ]);
    }

    /**
     * Toggle the active status of the specified occasion.
     */
    public function toggle(int $id): JsonResponse
    {
        $occasion = Occasion::find($id);

        if (!$occasion) {
            return response()->json([
                'success' => false,
                'message' => 'Occasion not found'
            ], 404);
        }

        $occasion->is_active = !$occasion->is_active;
        $occasion->save();

        return response()->json([
            'success' => true,
            'message' => 'Occasion status updated successfully',
            'data' => $occasion
        ]);
    }

    /**
     * Batch update sort orders.
     */
    public function reorder(Request $request): JsonResponse
    {
        $request->validate([
            'orders' => 'required|array',
            'orders.*.id' => 'required|integer|exists:occasions,id',
            'orders.*.sort_order' => 'required|integer',
        ]);

        foreach ($request->input('orders') as $item) {
            Occasion::where('id', $item['id'])->update(['sort_order' => $item['sort_order']]);
        }

        return response()->json([
            'success' => true,
            'message' => 'Occasions reordered successfully'
        ]);
    }

    /**
     * Remove the specified occasion from storage.
     */
    public function destroy(int $id): JsonResponse
    {
        $occasion = Occasion::find($id);

        if (!$occasion) {
            return response()->json([
                'success' => false,
                'message' => 'Occasion not found'
            ], 404);
        }

        $occasion->delete();

        return response()->json([
            'success' => true,
            'message' => 'Occasion deleted successfully'
        ]);
    }
}
