<?php

namespace App\Http\Controllers\Api\v1\Admin;

use App\Http\Controllers\Controller;
use App\Http\Resources\TagResource;
use App\Services\TagService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class TagController extends Controller
{
    protected $tagService;

    public function __construct(TagService $tagService)
    {
        $this->tagService = $tagService;
    }

    public function index(): JsonResponse
    {
        try {
            $tags = $this->tagService->getAllTags();

            return response()->json([
                'success' => true,
                'message' => 'Tags retrieved successfully',
                'data' => TagResource::collection($tags)
            ]);
        } catch (\Exception $e) {
            Log::error('TagController@index failed: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve tags',
                'error_code' => 'SERVER_ERROR'
            ], 500);
        }
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:100',
            'slug' => 'nullable|string|max:120',
        ]);

        try {
            $tag = $this->tagService->createTag($validated);

            return response()->json([
                'success' => true,
                'message' => 'Tag created successfully',
                'data' => new TagResource($tag)
            ], 201);
        } catch (\Exception $e) {
            Log::error('TagController@store failed: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to create tag',
                'error_code' => 'SERVER_ERROR'
            ], 500);
        }
    }

    public function show(int $id): JsonResponse
    {
        $tag = $this->tagService->getTagById($id);

        if (!$tag) {
            return response()->json([
                'success' => false,
                'message' => 'Tag not found',
                'error_code' => 'NOT_FOUND'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'message' => 'Tag retrieved successfully',
            'data' => new TagResource($tag)
        ]);
    }

    public function update(Request $request, int $id): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'sometimes|required|string|max:100',
            'slug' => 'nullable|string|max:120',
        ]);

        try {
            $tag = $this->tagService->updateTag($id, $validated);

            if (!$tag) {
                return response()->json([
                    'success' => false,
                    'message' => 'Tag not found',
                    'error_code' => 'NOT_FOUND'
                ], 404);
            }

            return response()->json([
                'success' => true,
                'message' => 'Tag updated successfully',
                'data' => new TagResource($tag)
            ]);
        } catch (\Exception $e) {
            Log::error('TagController@update failed: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to update tag',
                'error_code' => 'SERVER_ERROR'
            ], 500);
        }
    }

    public function destroy(int $id): JsonResponse
    {
        try {
            $deleted = $this->tagService->deleteTag($id);

            if (!$deleted) {
                return response()->json([
                    'success' => false,
                    'message' => 'Tag not found',
                    'error_code' => 'NOT_FOUND'
                ], 404);
            }

            return response()->json([
                'success' => true,
                'message' => 'Tag deleted successfully'
            ]);
        } catch (\Exception $e) {
            Log::error('TagController@destroy failed: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete tag',
                'error_code' => 'SERVER_ERROR'
            ], 500);
        }
    }
}
