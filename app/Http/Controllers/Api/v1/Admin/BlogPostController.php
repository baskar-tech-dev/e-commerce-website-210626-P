<?php

namespace App\Http\Controllers\Api\v1\Admin;

use App\Http\Controllers\Controller;
use App\Models\BlogPost;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class BlogPostController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = BlogPost::with(['category', 'author', 'tags']);

        // Search filter
        if ($request->has('search') && !empty($request->input('search'))) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhere('excerpt', 'like', "%{$search}%")
                  ->orWhere('content', 'like', "%{$search}%");
            });
        }

        // Status filter
        if ($request->has('status') && !empty($request->input('status'))) {
            $query->where('status', $request->input('status'));
        }

        // Category filter
        if ($request->has('blog_category_id') && !empty($request->input('blog_category_id'))) {
            $query->where('blog_category_id', $request->input('blog_category_id'));
        }

        $posts = $query->orderBy('created_at', 'desc')->paginate($request->input('per_page', 10));

        return response()->json([
            'success' => true,
            'data' => $posts->items(),
            'meta' => [
                'current_page' => $posts->currentPage(),
                'last_page' => $posts->lastPage(),
                'per_page' => $posts->perPage(),
                'total' => $posts->total(),
            ],
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'blog_category_id' => 'nullable|exists:blog_categories,id',
            'title' => 'required|string|max:300',
            'slug' => 'nullable|string|max:350|unique:blog_posts,slug',
            'excerpt' => 'nullable|string|max:500',
            'content' => 'required|string',
            'featured_image' => 'nullable|string|max:500',
            'status' => 'required|string|in:draft,published,archived',
            'is_featured' => 'nullable|boolean',
            'meta_title' => 'nullable|string|max:200',
            'meta_description' => 'nullable|string|max:500',
            'tags' => 'nullable|array',
            'tags.*' => 'exists:blog_tags,id',
        ]);

        // Auto-detect author
        $authorId = auth()->id() ?? User::first()?->id;
        $validated['author_id'] = $authorId;

        if ($validated['status'] === 'published') {
            $validated['published_at'] = now();
        }

        $post = BlogPost::create($validated);

        if ($request->has('tags')) {
            $post->tags()->sync($request->input('tags'));
        }

        return response()->json([
            'success' => true,
            'message' => 'Blog post created successfully',
            'data' => $post->load(['category', 'tags', 'author']),
        ]);
    }

    public function show(int $id): JsonResponse
    {
        $post = BlogPost::with(['category', 'tags', 'author'])->findOrFail($id);
        return response()->json([
            'success' => true,
            'data' => $post,
        ]);
    }

    public function update(Request $request, int $id): JsonResponse
    {
        $post = BlogPost::findOrFail($id);

        $validated = $request->validate([
            'blog_category_id' => 'nullable|exists:blog_categories,id',
            'title' => 'required|string|max:300',
            'slug' => 'required|string|max:350|unique:blog_posts,slug,' . $id,
            'excerpt' => 'nullable|string|max:500',
            'content' => 'required|string',
            'featured_image' => 'nullable|string|max:500',
            'status' => 'required|string|in:draft,published,archived',
            'is_featured' => 'nullable|boolean',
            'meta_title' => 'nullable|string|max:200',
            'meta_description' => 'nullable|string|max:500',
            'tags' => 'nullable|array',
            'tags.*' => 'exists:blog_tags,id',
        ]);

        if ($validated['status'] === 'published' && $post->status !== 'published') {
            $validated['published_at'] = now();
        }

        $post->update($validated);

        if ($request->has('tags')) {
            $post->tags()->sync($request->input('tags'));
        } else {
            $post->tags()->detach();
        }

        return response()->json([
            'success' => true,
            'message' => 'Blog post updated successfully',
            'data' => $post->load(['category', 'tags', 'author']),
        ]);
    }

    public function destroy(int $id): JsonResponse
    {
        $post = BlogPost::findOrFail($id);
        $post->delete();

        return response()->json([
            'success' => true,
            'message' => 'Blog post deleted successfully',
        ]);
    }
}
