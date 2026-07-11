<?php

namespace App\Http\Controllers\Api\v1\Admin;

use App\Http\Controllers\Controller;
use App\Models\BlogCategory;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class BlogCategoryController extends Controller
{
    public function index(): JsonResponse
    {
        $categories = BlogCategory::orderBy('sort_order', 'asc')->orderBy('name', 'asc')->get();
        return response()->json([
            'success' => true,
            'data' => $categories,
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:150',
            'slug' => 'nullable|string|max:180|unique:blog_categories,slug',
            'description' => 'nullable|string',
            'sort_order' => 'nullable|integer',
            'is_active' => 'nullable|boolean',
        ]);

        if (empty($validated['slug'])) {
            $validated['slug'] = Str::slug($validated['name']);
            // make unique
            $slug = $validated['slug'];
            $counter = 1;
            while (BlogCategory::where('slug', $slug)->exists()) {
                $slug = $validated['slug'] . '-' . $counter;
                $counter++;
            }
            $validated['slug'] = $slug;
        }

        $category = BlogCategory::create($validated);

        return response()->json([
            'success' => true,
            'message' => 'Blog category created successfully',
            'data' => $category,
        ]);
    }

    public function show(int $id): JsonResponse
    {
        $category = BlogCategory::findOrFail($id);
        return response()->json([
            'success' => true,
            'data' => $category,
        ]);
    }

    public function update(Request $request, int $id): JsonResponse
    {
        $category = BlogCategory::findOrFail($id);

        $validated = $request->validate([
            'name' => 'required|string|max:150',
            'slug' => 'required|string|max:180|unique:blog_categories,slug,' . $id,
            'description' => 'nullable|string',
            'sort_order' => 'nullable|integer',
            'is_active' => 'nullable|boolean',
        ]);

        $category->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'Blog category updated successfully',
            'data' => $category,
        ]);
    }

    public function destroy(int $id): JsonResponse
    {
        $category = BlogCategory::findOrFail($id);
        $category->delete();

        return response()->json([
            'success' => true,
            'message' => 'Blog category deleted successfully',
        ]);
    }
}
