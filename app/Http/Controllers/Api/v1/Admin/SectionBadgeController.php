<?php

namespace App\Http\Controllers\Api\v1\Admin;

use App\Http\Controllers\Controller;
use App\Models\SectionBadge;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SectionBadgeController extends Controller
{
    /**
     * Admin listing with active/inactive counts.
     */
    public function index(Request $request): JsonResponse
    {
        $status = $request->input('status', 'all');

        $query = SectionBadge::query()->orderBy('sort_order', 'asc')->orderBy('id', 'asc');

        if ($status === 'active') {
            $query->where('is_active', true);
        } elseif ($status === 'inactive') {
            $query->where('is_active', false);
        }

        $badges = $query->get();

        $totalCount = SectionBadge::count();
        $activeCount = SectionBadge::where('is_active', true)->count();
        $inactiveCount = SectionBadge::where('is_active', false)->count();

        return response()->json([
            'success' => true,
            'data' => $badges,
            'meta' => [
                'total' => $totalCount,
                'active_count' => $activeCount,
                'inactive_count' => $inactiveCount,
            ]
        ]);
    }

    /**
     * Store a new section badge.
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'title' => 'required|string|max:100',
            'filter_type' => 'required|string|in:badge,new_arrival,bestseller,featured,occasion',
            'badge_key' => 'nullable|string|max:100',
            'is_active' => 'boolean',
            'sort_order' => 'integer',
        ]);

        if (empty($validated['badge_key'])) {
            $validated['badge_key'] = $validated['title'];
        }

        if (!isset($validated['sort_order'])) {
            $validated['sort_order'] = SectionBadge::max('sort_order') + 1;
        }

        $badge = SectionBadge::create($validated);

        return response()->json([
            'success' => true,
            'message' => 'Section badge created successfully',
            'data' => $badge,
        ], 201);
    }

    /**
     * Update an existing badge.
     */
    public function update(Request $request, $id): JsonResponse
    {
        $badge = SectionBadge::findOrFail($id);

        $validated = $request->validate([
            'title' => 'required|string|max:100',
            'filter_type' => 'required|string|in:badge,new_arrival,bestseller,featured,occasion',
            'badge_key' => 'nullable|string|max:100',
            'is_active' => 'boolean',
            'sort_order' => 'integer',
        ]);

        if (empty($validated['badge_key'])) {
            $validated['badge_key'] = $validated['title'];
        }

        $badge->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'Section badge updated successfully',
            'data' => $badge,
        ]);
    }

    /**
     * Toggle active / inactive status.
     */
    public function toggle($id): JsonResponse
    {
        $badge = SectionBadge::findOrFail($id);
        $badge->is_active = !$badge->is_active;
        $badge->save();

        return response()->json([
            'success' => true,
            'message' => 'Section badge status updated',
            'data' => $badge,
        ]);
    }

    /**
     * Reorder badges (swap or batch reorder).
     */
    public function reorder(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'orders' => 'required|array', // e.g. [{id: 1, sort_order: 1}, ...]
            'orders.*.id' => 'required|exists:section_badges,id',
            'orders.*.sort_order' => 'required|integer',
        ]);

        foreach ($validated['orders'] as $item) {
            SectionBadge::where('id', $item['id'])->update(['sort_order' => $item['sort_order']]);
        }

        return response()->json([
            'success' => true,
            'message' => 'Badges reordered successfully',
        ]);
    }

    /**
     * Delete a badge.
     */
    public function destroy($id): JsonResponse
    {
        $badge = SectionBadge::findOrFail($id);
        $badge->delete();

        return response()->json([
            'success' => true,
            'message' => 'Section badge deleted successfully',
        ]);
    }
}
