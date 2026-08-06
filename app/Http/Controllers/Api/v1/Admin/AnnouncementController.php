<?php

namespace App\Http\Controllers\Api\v1\Admin;

use App\Http\Controllers\Controller;
use App\Models\Announcement;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class AnnouncementController extends Controller
{
    /**
     * Display a listing of announcements.
     * Supports filtering by status: 'all', 'active', 'inactive'.
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $query = Announcement::query();

            $status = $request->query('status', 'all');
            if ($status === 'active') {
                $query->where('is_active', true);
            } elseif ($status === 'inactive') {
                $query->where('is_active', false);
            }

            $announcements = $query->orderBy('sort_order', 'asc')
                                  ->orderBy('id', 'desc')
                                  ->get();

            $activeCount = Announcement::where('is_active', true)->count();
            $inactiveCount = Announcement::where('is_active', false)->count();

            return response()->json([
                'success' => true,
                'data' => $announcements,
                'meta' => [
                    'total' => $announcements->count(),
                    'active_count' => $activeCount,
                    'inactive_count' => $inactiveCount,
                ]
            ]);
        } catch (\Exception $e) {
            Log::error('AnnouncementController@index failed: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve announcements',
            ], 500);
        }
    }

    /**
     * Store a newly created announcement.
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'text' => 'required|string|max:500',
            'link' => 'nullable|string|max:255',
            'is_active' => 'boolean',
            'sort_order' => 'integer',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date',
        ]);

        try {
            $announcement = Announcement::create([
                'text' => $validated['text'],
                'link' => $validated['link'] ?? null,
                'is_active' => $validated['is_active'] ?? true,
                'sort_order' => $validated['sort_order'] ?? 0,
                'start_date' => $validated['start_date'] ?? null,
                'end_date' => $validated['end_date'] ?? null,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Announcement created successfully',
                'data' => $announcement,
            ], 201);
        } catch (\Exception $e) {
            Log::error('AnnouncementController@store failed: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to create announcement',
            ], 500);
        }
    }

    /**
     * Display the specified announcement.
     */
    public function show($id): JsonResponse
    {
        try {
            $announcement = Announcement::findOrFail($id);
            return response()->json([
                'success' => true,
                'data' => $announcement,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Announcement not found',
            ], 404);
        }
    }

    /**
     * Update the specified announcement.
     */
    public function update(Request $request, $id): JsonResponse
    {
        $validated = $request->validate([
            'text' => 'required|string|max:500',
            'link' => 'nullable|string|max:255',
            'is_active' => 'boolean',
            'sort_order' => 'integer',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date',
        ]);

        try {
            $announcement = Announcement::findOrFail($id);
            $announcement->update($validated);

            return response()->json([
                'success' => true,
                'message' => 'Announcement updated successfully',
                'data' => $announcement,
            ]);
        } catch (\Exception $e) {
            Log::error('AnnouncementController@update failed: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to update announcement',
            ], 500);
        }
    }

    /**
     * Remove the specified announcement.
     */
    public function destroy($id): JsonResponse
    {
        try {
            $announcement = Announcement::findOrFail($id);
            $announcement->delete();

            return response()->json([
                'success' => true,
                'message' => 'Announcement deleted successfully',
            ]);
        } catch (\Exception $e) {
            Log::error('AnnouncementController@destroy failed: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete announcement',
            ], 500);
        }
    }

    /**
     * Quick toggle active status.
     */
    public function toggleActive($id): JsonResponse
    {
        try {
            $announcement = Announcement::findOrFail($id);
            $announcement->is_active = !$announcement->is_active;
            $announcement->save();

            return response()->json([
                'success' => true,
                'message' => 'Status updated successfully',
                'data' => $announcement,
            ]);
        } catch (\Exception $e) {
            Log::error('AnnouncementController@toggleActive failed: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to toggle status',
            ], 500);
        }
    }

    /**
     * Reorder announcements.
     */
    public function reorder(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'order' => 'required|array',
            'order.*.id' => 'required|exists:announcements,id',
            'order.*.sort_order' => 'required|integer',
        ]);

        try {
            foreach ($validated['order'] as $item) {
                Announcement::where('id', $item['id'])->update(['sort_order' => $item['sort_order']]);
            }

            return response()->json([
                'success' => true,
                'message' => 'Sort order updated successfully',
            ]);
        } catch (\Exception $e) {
            Log::error('AnnouncementController@reorder failed: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to reorder announcements',
            ], 500);
        }
    }
}
