<?php

namespace App\Http\Controllers\Api\v1\Admin;

use App\Http\Controllers\Controller;
use App\Models\HeroSlide;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class HeroSlideController extends Controller
{
    /**
     * Display a listing of hero slides for admin.
     */
    public function index(): JsonResponse
    {
        $slides = HeroSlide::orderBy('sort_order', 'asc')->orderBy('id', 'asc')->get();

        return response()->json([
            'success' => true,
            'data' => $slides,
        ]);
    }

    /**
     * Store a newly created hero slide.
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'tag' => 'nullable|string|max:100',
            'script_text' => 'nullable|string|max:100',
            'title' => 'required|string|max:255',
            'description' => 'nullable|string|max:1000',
            'left_image' => 'required|string|max:500',
            'right_image' => 'nullable|string|max:500',
            'button_text' => 'nullable|string|max:50',
            'button_link' => 'nullable|string|max:255',
            'sort_order' => 'nullable|integer',
            'is_active' => 'nullable|boolean',
        ]);

        if (!isset($validated['sort_order'])) {
            $maxOrder = HeroSlide::max('sort_order') ?? 0;
            $validated['sort_order'] = $maxOrder + 1;
        }

        $validated['is_active'] = $validated['is_active'] ?? true;
        $validated['button_text'] = $validated['button_text'] ?? 'SHOP NOW';
        $validated['button_link'] = $validated['button_link'] ?? '/shop';

        $slide = HeroSlide::create($validated);

        return response()->json([
            'success' => true,
            'message' => 'Hero banner slide created successfully',
            'data' => $slide,
        ], 201);
    }

    /**
     * Display the specified hero slide.
     */
    public function show($id): JsonResponse
    {
        $slide = HeroSlide::findOrFail($id);

        return response()->json([
            'success' => true,
            'data' => $slide,
        ]);
    }

    /**
     * Update the specified hero slide.
     */
    public function update(Request $request, $id): JsonResponse
    {
        $slide = HeroSlide::findOrFail($id);

        $validated = $request->validate([
            'tag' => 'nullable|string|max:100',
            'script_text' => 'nullable|string|max:100',
            'title' => 'required|string|max:255',
            'description' => 'nullable|string|max:1000',
            'left_image' => 'required|string|max:500',
            'right_image' => 'nullable|string|max:500',
            'button_text' => 'nullable|string|max:50',
            'button_link' => 'nullable|string|max:255',
            'sort_order' => 'nullable|integer',
            'is_active' => 'nullable|boolean',
        ]);

        $slide->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'Hero banner slide updated successfully',
            'data' => $slide,
        ]);
    }

    /**
     * Remove the specified hero slide.
     */
    public function destroy($id): JsonResponse
    {
        $slide = HeroSlide::findOrFail($id);
        $slide->delete();

        return response()->json([
            'success' => true,
            'message' => 'Hero banner slide deleted successfully',
        ]);
    }

    /**
     * Reorder hero slides.
     */
    public function reorder(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'slides' => 'required|array',
            'slides.*.id' => 'required|exists:hero_slides,id',
            'slides.*.sort_order' => 'required|integer',
        ]);

        foreach ($validated['slides'] as $item) {
            HeroSlide::where('id', $item['id'])->update(['sort_order' => $item['sort_order']]);
        }

        return response()->json([
            'success' => true,
            'message' => 'Hero slides reordered successfully',
        ]);
    }

    /**
     * Upload an image file for hero slide banner.
     */
    public function uploadImage(Request $request): JsonResponse
    {
        $request->validate([
            'image' => 'required|image|mimes:jpeg,png,jpg,webp,gif|max:5120', // up to 5MB
        ]);

        if ($request->hasFile('image')) {
            $file = $request->file('image');
            $filename = 'hero_' . time() . '_' . Str::random(8) . '.' . $file->getClientOriginalExtension();
            
            // Store in public storage directory
            $path = $file->storeAs('hero_banners', $filename, 'public');
            $url = Storage::url($path);

            return response()->json([
                'success' => true,
                'message' => 'Banner image uploaded successfully',
                'url' => $url,
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => 'No image file uploaded',
        ], 400);
    }
}
