<?php

namespace App\Http\Controllers\Api\v1\Admin;

use App\Http\Controllers\Controller;
use App\Models\InstagramReel;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class InstagramReelController extends Controller
{
    /**
     * Display a listing of the reels/videos.
     */
    public function index(): JsonResponse
    {
        try {
            $reels = InstagramReel::orderBy('sort_order', 'asc')
                                  ->orderBy('id', 'desc')
                                  ->get();

            return response()->json([
                'success' => true,
                'data' => $reels,
            ]);
        } catch (\Exception $e) {
            Log::error('InstagramReelController@index failed: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve reels',
            ], 500);
        }
    }

    /**
     * Store a newly created reel/video.
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'caption' => 'nullable|string|max:255',
            'type' => 'required|string|in:file,youtube',
            'video_url' => 'nullable|string',
            'instagram_url' => 'nullable|string|url',
            'is_active' => 'boolean',
            'sort_order' => 'integer',
        ]);

        try {
            $reel = InstagramReel::create($validated);

            return response()->json([
                'success' => true,
                'message' => 'Reel created successfully',
                'data' => $reel,
            ], 201);
        } catch (\Exception $e) {
            Log::error('InstagramReelController@store failed: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to create reel',
            ], 500);
        }
    }

    /**
     * Display the specified reel/video.
     */
    public function show(int $id): JsonResponse
    {
        try {
            $reel = InstagramReel::findOrFail($id);

            return response()->json([
                'success' => true,
                'data' => $reel,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Reel not found',
            ], 404);
        }
    }

    /**
     * Update the specified reel/video.
     */
    public function update(Request $request, int $id): JsonResponse
    {
        $validated = $request->validate([
            'caption' => 'nullable|string|max:255',
            'type' => 'sometimes|required|string|in:file,youtube',
            'video_url' => 'nullable|string',
            'instagram_url' => 'nullable|string|url',
            'is_active' => 'boolean',
            'sort_order' => 'integer',
        ]);

        try {
            $reel = InstagramReel::findOrFail($id);
            $oldUrl = $reel->video_url;

            $reel->update($validated);

            // Clean up old local video if it changed
            if (isset($validated['video_url']) && $validated['video_url'] !== $oldUrl) {
                $this->deletePhysicalFile($oldUrl);
            }

            return response()->json([
                'success' => true,
                'message' => 'Reel updated successfully',
                'data' => $reel,
            ]);
        } catch (\Exception $e) {
            Log::error('InstagramReelController@update failed: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to update reel',
            ], 500);
        }
    }

    /**
     * Remove the specified reel/video.
     */
    public function destroy(int $id): JsonResponse
    {
        try {
            $reel = InstagramReel::findOrFail($id);
            $videoUrl = $reel->video_url;

            $reel->delete();

            // Clean up the physical file
            $this->deletePhysicalFile($videoUrl);

            return response()->json([
                'success' => true,
                'message' => 'Reel deleted successfully',
            ]);
        } catch (\Exception $e) {
            Log::error('InstagramReelController@destroy failed: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete reel',
            ], 500);
        }
    }

    /**
     * Upload a video file to local storage.
     */
    public function uploadVideo(Request $request): JsonResponse
    {
        $request->validate([
            'file' => 'required|file|mimes:mp4,mov,ogg,webm,quicktime|max:51200', // max 50MB
        ]);

        try {
            $file = $request->file('file');
            
            // Ensure the reels directory exists
            Storage::disk('public')->makeDirectory('reels');
            
            $fileName = Str::uuid()->toString() . '.' . $file->getClientOriginalExtension();
            $path = $file->storeAs('reels', $fileName, 'public');
            
            $url = '/storage/' . $path;

            return response()->json([
                'success' => true,
                'message' => 'Video uploaded successfully.',
                'url' => $url,
            ]);
        } catch (\Exception $e) {
            Log::error('InstagramReelController@uploadVideo failed: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to upload video file: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Helper to delete physical storage files by public URL.
     */
    protected function deletePhysicalFile(?string $url): void
    {
        if (empty($url)) return;

        // E.g. URL: /storage/reels/filename.mp4 -> reels/filename.mp4
        if (str_starts_with($url, '/storage/')) {
            $path = preg_replace('/^\/?storage\//', '', parse_url($url, PHP_URL_PATH));
            if (Storage::disk('public')->exists($path)) {
                Storage::disk('public')->delete($path);
            }
        }
    }
}
