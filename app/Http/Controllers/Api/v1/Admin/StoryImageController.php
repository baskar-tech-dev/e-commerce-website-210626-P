<?php

namespace App\Http\Controllers\Api\v1\Admin;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class StoryImageController extends Controller
{
    /**
     * Default fallback story image path.
     */
    const DEFAULT_STORY_IMAGE = '/asset/mother.jpg';
    const DEFAULT_FIT = 'contain';
    const DEFAULT_POSITION = 'top center';

    /**
     * Get story image setting (Public Storefront & Admin read).
     */
    public function getStorySettings(): JsonResponse
    {
        $storyImage = Setting::get('story_image', 'homepage', self::DEFAULT_STORY_IMAGE);
        $fit = Setting::get('story_image_fit', 'homepage', self::DEFAULT_FIT);
        $position = Setting::get('story_image_position', 'homepage', self::DEFAULT_POSITION);

        return response()->json([
            'success' => true,
            'data' => [
                'story_image' => $storyImage ?: self::DEFAULT_STORY_IMAGE,
                'story_image_fit' => $fit ?: self::DEFAULT_FIT,
                'story_image_position' => $position ?: self::DEFAULT_POSITION,
                'default_image' => self::DEFAULT_STORY_IMAGE,
            ],
        ]);
    }

    /**
     * Get story image configuration for admin.
     */
    public function show(Request $request): JsonResponse
    {
        $storyImage = Setting::get('story_image', 'homepage', self::DEFAULT_STORY_IMAGE);
        $fit = Setting::get('story_image_fit', 'homepage', self::DEFAULT_FIT);
        $position = Setting::get('story_image_position', 'homepage', self::DEFAULT_POSITION);

        return response()->json([
            'success' => true,
            'data' => [
                'story_image' => $storyImage ?: self::DEFAULT_STORY_IMAGE,
                'story_image_fit' => $fit ?: self::DEFAULT_FIT,
                'story_image_position' => $position ?: self::DEFAULT_POSITION,
                'default_image' => self::DEFAULT_STORY_IMAGE,
                'is_custom' => $storyImage && $storyImage !== self::DEFAULT_STORY_IMAGE,
            ],
        ]);
    }

    /**
     * Upload and set a new story founder image (Super Admin Only).
     */
    public function uploadStoryImage(Request $request): JsonResponse
    {
        $this->authorizeSuperAdmin($request);

        $request->validate([
            'image' => 'required|image|mimes:jpeg,png,jpg,webp,avif|max:10240', // max 10MB
            'fit' => 'nullable|string|in:contain,cover,fill,scale-down',
            'position' => 'nullable|string|max:50',
        ]);

        if ($request->hasFile('image')) {
            $file = $request->file('image');
            $filename = 'story_' . time() . '_' . Str::random(8) . '.' . $file->getClientOriginalExtension();
            
            // Store in public disk under story directory
            $path = $file->storeAs('story', $filename, 'public');
            $url = Storage::url($path);

            Setting::set('story_image', $url, 'homepage', 'text');
            
            if ($request->has('fit')) {
                Setting::set('story_image_fit', $request->input('fit'), 'homepage', 'text');
            }
            if ($request->has('position')) {
                Setting::set('story_image_position', $request->input('position'), 'homepage', 'text');
            }

            $fit = Setting::get('story_image_fit', 'homepage', self::DEFAULT_FIT);
            $position = Setting::get('story_image_position', 'homepage', self::DEFAULT_POSITION);

            return response()->json([
                'success' => true,
                'message' => 'Our Story founder image uploaded and updated successfully.',
                'data' => [
                    'story_image' => $url,
                    'story_image_fit' => $fit,
                    'story_image_position' => $position,
                    'default_image' => self::DEFAULT_STORY_IMAGE,
                    'is_custom' => true,
                ],
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => 'No image file uploaded.',
        ], 400);
    }

    /**
     * Update story image URL, fit, position or reset to default (Super Admin Only).
     */
    public function updateStoryImage(Request $request): JsonResponse
    {
        $this->authorizeSuperAdmin($request);

        $validated = $request->validate([
            'image_url' => 'nullable|string|max:1000',
            'fit' => 'nullable|string|in:contain,cover,fill,scale-down',
            'position' => 'nullable|string|max:50',
            'reset' => 'nullable|boolean',
        ]);

        if (!empty($validated['reset'])) {
            Setting::set('story_image', self::DEFAULT_STORY_IMAGE, 'homepage', 'text');
            Setting::set('story_image_fit', self::DEFAULT_FIT, 'homepage', 'text');
            Setting::set('story_image_position', self::DEFAULT_POSITION, 'homepage', 'text');
            $url = self::DEFAULT_STORY_IMAGE;
            $fit = self::DEFAULT_FIT;
            $position = self::DEFAULT_POSITION;
            $message = 'Our Story image reset to default successfully.';
            $isCustom = false;
        } else {
            $url = $validated['image_url'] ?? Setting::get('story_image', 'homepage', self::DEFAULT_STORY_IMAGE);
            Setting::set('story_image', $url, 'homepage', 'text');
            
            if (isset($validated['fit'])) {
                Setting::set('story_image_fit', $validated['fit'], 'homepage', 'text');
            }
            if (isset($validated['position'])) {
                Setting::set('story_image_position', $validated['position'], 'homepage', 'text');
            }

            $fit = Setting::get('story_image_fit', 'homepage', self::DEFAULT_FIT);
            $position = Setting::get('story_image_position', 'homepage', self::DEFAULT_POSITION);

            $message = 'Our Story image updated successfully.';
            $isCustom = ($url !== self::DEFAULT_STORY_IMAGE);
        }

        return response()->json([
            'success' => true,
            'message' => $message,
            'data' => [
                'story_image' => $url,
                'story_image_fit' => $fit,
                'story_image_position' => $position,
                'default_image' => self::DEFAULT_STORY_IMAGE,
                'is_custom' => $isCustom,
            ],
        ]);
    }

    /**
     * Helper to verify that the authenticated user is a Super Admin.
     */
    protected function authorizeSuperAdmin(Request $request): void
    {
        $user = $request->user();

        $isSuperAdmin = $user && (
            $user->hasRole('super_admin') ||
            (int)$user->role_id === 1 ||
            ($user->relationLoaded('role') && $user->role?->name === 'super_admin')
        );

        if (!$isSuperAdmin) {
            abort(response()->json([
                'success' => false,
                'message' => 'Access Denied. Only Super Admin has permission to modify the story image.',
                'error_code' => 'FORBIDDEN',
            ], 403));
        }
    }
}
