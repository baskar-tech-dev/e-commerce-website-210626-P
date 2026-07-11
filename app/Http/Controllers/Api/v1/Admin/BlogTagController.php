<?php

namespace App\Http\Controllers\Api\v1\Admin;

use App\Http\Controllers\Controller;
use App\Models\BlogTag;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class BlogTagController extends Controller
{
    public function index(): JsonResponse
    {
        $tags = BlogTag::orderBy('name', 'asc')->get();
        return response()->json([
            'success' => true,
            'data' => $tags,
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:100',
            'slug' => 'nullable|string|max:120|unique:blog_tags,slug',
        ]);

        if (empty($validated['slug'])) {
            $validated['slug'] = Str::slug($validated['name']);
            $slug = $validated['slug'];
            $counter = 1;
            while (BlogTag::where('slug', $slug)->exists()) {
                $slug = $validated['slug'] . '-' . $counter;
                $counter++;
            }
            $validated['slug'] = $slug;
        }

        $tag = BlogTag::create($validated);

        return response()->json([
            'success' => true,
            'message' => 'Blog tag created successfully',
            'data' => $tag,
        ]);
    }

    public function show(int $id): JsonResponse
    {
        $tag = BlogTag::findOrFail($id);
        return response()->json([
            'success' => true,
            'data' => $tag,
        ]);
    }

    public function update(Request $request, int $id): JsonResponse
    {
        $tag = BlogTag::findOrFail($id);

        $validated = $request->validate([
            'name' => 'required|string|max:100',
            'slug' => 'required|string|max:120|unique:blog_tags,slug,' . $id,
        ]);

        $tag->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'Blog tag updated successfully',
            'data' => $tag,
        ]);
    }

    public function destroy(int $id): JsonResponse
    {
        $tag = BlogTag::findOrFail($id);
        $tag->delete();

        return response()->json([
            'success' => true,
            'message' => 'Blog tag deleted successfully',
        ]);
    }
}
