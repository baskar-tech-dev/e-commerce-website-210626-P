<?php

namespace App\Http\Controllers\Api\v1\Admin;

use App\Http\Controllers\Controller;
use App\Models\Occasion;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
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

    /**
     * Upload and compress an occasion card image.
     */
    public function uploadImage(Request $request): JsonResponse
    {
        $request->validate([
            'image' => 'required|image|mimes:jpeg,png,jpg,webp,avif|max:30720',
        ]);

        $file = $request->file('image');
        $fileName = Str::uuid()->toString();
        $folder = 'occasions';
        
        Storage::disk('public')->makeDirectory($folder);

        $ext = $file->getClientOriginalExtension();
        $tempPath = $file->storeAs("{$folder}/temp", "{$fileName}.{$ext}", 'public');
        $sourcePath = Storage::disk('public')->path($tempPath);

        $webpRelPath = "{$folder}/{$fileName}.webp";
        $destPath = Storage::disk('public')->path($webpRelPath);

        $resized = $this->resizeAndConvertWebp($sourcePath, $destPath, 800, 85);
        if (!$resized && !file_exists($destPath)) {
            @copy($sourcePath, $destPath);
        }

        // Clean up temp original
        if (file_exists($sourcePath)) {
            @unlink($sourcePath);
        }

        $url = '/storage/' . $webpRelPath;

        return response()->json([
            'success' => true,
            'message' => 'Occasion image uploaded and optimized successfully.',
            'data' => [
                'url' => $url,
            ]
        ]);
    }

    /**
     * Helper to resize and convert image to WebP format.
     */
    private function resizeAndConvertWebp(string $sourcePath, string $destinationPath, int $targetWidth = 800, int $quality = 85): bool
    {
        if (!extension_loaded('gd')) {
            return @copy($sourcePath, $destinationPath);
        }

        $info = @getimagesize($sourcePath);
        if (!$info) {
            return @copy($sourcePath, $destinationPath);
        }

        $mime = $info['mime'] ?? '';
        $width = $info[0] ?? 0;
        $height = $info[1] ?? 0;

        if ($width <= 0 || $height <= 0) {
            return @copy($sourcePath, $destinationPath);
        }

        $image = null;
        switch ($mime) {
            case 'image/jpeg':
            case 'image/jpg':
                if (function_exists('imagecreatefromjpeg')) {
                    $image = @imagecreatefromjpeg($sourcePath);
                }
                break;
            case 'image/png':
                if (function_exists('imagecreatefrompng')) {
                    $image = @imagecreatefrompng($sourcePath);
                    if ($image) {
                        imagepalettetotruecolor($image);
                        imagealphablending($image, true);
                        imagesavealpha($image, true);
                    }
                }
                break;
            case 'image/webp':
                if (function_exists('imagecreatefromwebp')) {
                    $image = @imagecreatefromwebp($sourcePath);
                }
                break;
            default:
                return @copy($sourcePath, $destinationPath);
        }

        if (!$image) {
            return @copy($sourcePath, $destinationPath);
        }

        $aspectRatio = $width / $height;
        if ($width > $targetWidth) {
            $newWidth = $targetWidth;
            $newHeight = intval($targetWidth / $aspectRatio);
        } else {
            $newWidth = $width;
            $newHeight = $height;
        }

        $newImage = imagecreatetruecolor($newWidth, $newHeight);
        $success = false;

        if ($newImage) {
            imagealphablending($newImage, false);
            imagesavealpha($newImage, true);
            imagecopyresampled($newImage, $image, 0, 0, 0, 0, $newWidth, $newHeight, $width, $height);

            if (function_exists('imagewebp')) {
                $success = @imagewebp($newImage, $destinationPath, $quality);
            } elseif (function_exists('imagejpeg')) {
                $success = @imagejpeg($newImage, $destinationPath, $quality);
            }
            imagedestroy($newImage);
        }

        imagedestroy($image);

        return $success || file_exists($destinationPath);
    }
}
