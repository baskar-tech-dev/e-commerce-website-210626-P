<?php

namespace App\Http\Controllers\Api\v1\Admin;

use App\Http\Controllers\Controller;
use App\Models\ProductImage;
use App\Models\ImageMetadata;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class MediaController extends Controller
{
    /**
     * Upload and process image.
     */
    public function upload(Request $request): JsonResponse
    {
        $request->validate([
            'file' => 'required|image|mimes:jpeg,png,jpg,webp,avif|max:5120', // max 5MB limit
            'product_id' => 'nullable|integer|exists:products,id',
            'category_id' => 'nullable|integer|exists:categories,id',
            'folder' => 'nullable|string|max:50',
            'color_group' => 'nullable|string|max:50',
            'variant_id' => 'nullable|integer|exists:product_variants,id',
        ]);

        $file = $request->file('file');
        $hash = hash_file('sha256', $file->getRealPath());

        // 1. Check for duplicates
        $duplicate = ImageMetadata::where('hash', $hash)->first();
        if ($duplicate) {
            $existingImage = ProductImage::find($duplicate->product_image_id);
            if ($existingImage) {
                return response()->json([
                    'success' => true,
                    'message' => 'Duplicate image detected. Reusing existing upload.',
                    'data' => [
                        'id' => $existingImage->id,
                        'url' => $existingImage->url,
                        'thumbnail_url' => $existingImage->thumbnail_url,
                        'color_group' => $existingImage->color_group,
                        'variant_id' => $existingImage->variant_id,
                    ]
                ]);
            }
        }

        // Expose path definitions
        $productId = $request->input('product_id');
        $categoryId = $request->input('category_id');
        $folderType = $request->input('folder');
        $colorGroup = $request->input('color_group');
        $variantId = $request->input('variant_id');

        if ($categoryId || $folderType === 'categories') {
            $folder = $categoryId ? "categories/{$categoryId}" : "categories";
        } elseif ($productId) {
            $folder = "products/{$productId}";
        } else {
            $folder = "temp";
        }
        $fileName = Str::uuid()->toString();

        // 2. Setup Storage Directory relative to public disk root (storage/app/public)
        $originalDir = "{$folder}/original";
        $webpDir = "{$folder}/webp";
        $thumbDir = "{$folder}/thumbnails";

        Storage::disk('public')->makeDirectory($originalDir);
        Storage::disk('public')->makeDirectory($webpDir);
        Storage::disk('public')->makeDirectory($thumbDir);

        // Save original file on public disk
        $originalExt = $file->getClientOriginalExtension();
        $originalPath = $file->storeAs($originalDir, "{$fileName}.{$originalExt}", 'public');
        $sourcePath = Storage::disk('public')->path($originalPath);

        // 3. Process image to webp versions (Full Size & Thumbnail)
        $webpPath = "{$folder}/webp/{$fileName}.webp";
        $thumbPath = "{$folder}/thumbnails/{$fileName}_thumb.webp";

        $fullWebpPath = Storage::disk('public')->path($webpPath);
        $fullThumbPath = Storage::disk('public')->path($thumbPath);

        $successWebp = $this->resizeAndConvertWebp($sourcePath, $fullWebpPath, 1200);
        $successThumb = $this->resizeAndConvertWebp($sourcePath, $fullThumbPath, 200);

        if (!$successWebp || !$successThumb) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to process and compress image using GD.',
            ], 500);
        }

        // Retrieve Dimensions
        list($width, $height) = getimagesize($sourcePath);

        $url = '/storage/' . $webpPath;
        $thumbnailUrl = '/storage/' . $thumbPath;

        // If product ID is provided, store directly in database
        $imageRecord = null;
        if ($productId) {
            $imageRecord = ProductImage::create([
                'product_id' => $productId,
                'variant_id' => $variantId,
                'color_group' => $colorGroup,
                'url' => $url,
                'thumbnail_url' => $thumbnailUrl,
                'alt_text' => $request->input('alt_text', 'Product image'),
                'is_primary' => false,
            ]);

            ImageMetadata::create([
                'product_image_id' => $imageRecord->id,
                'width' => $width,
                'height' => $height,
                'file_size' => $file->getSize(),
                'format' => 'webp',
                'hash' => $hash,
                'uploaded_by' => $request->user()?->id,
            ]);
        }

        return response()->json([
            'success' => true,
            'message' => 'Image uploaded and optimized successfully.',
            'data' => [
                'id' => $imageRecord ? $imageRecord->id : null,
                'url' => $url,
                'thumbnail_url' => $thumbnailUrl,
                'temp_path' => !$productId ? [
                    'original' => $originalPath,
                    'webp' => $webpPath,
                    'thumb' => $thumbPath,
                    'hash' => $hash,
                    'width' => $width,
                    'height' => $height,
                    'file_size' => $file->getSize(),
                ] : null,
                'color_group' => $colorGroup,
                'variant_id' => $variantId,
            ]
        ]);
    }

    /**
     * Validate an image's upload characteristics.
     */
    public function validateImage(Request $request): JsonResponse
    {
        $request->validate([
            'file' => 'required|file|max:10240',
        ]);

        $file = $request->file('file');
        $mime = $file->getMimeType();

        // 1. Security Check - Executable & MIME scripts
        $allowedMimes = ['image/jpeg', 'image/jpg', 'image/pjpeg', 'image/png', 'image/webp', 'image/avif'];
        if (!in_array($mime, $allowedMimes)) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid file format. Supported formats: JPG, JPEG, PNG, WEBP, AVIF. Executable scripts / SVG inline vectors are prohibited.',
            ], 422);
        }

        list($width, $height) = getimagesize($file->getRealPath());

        // 2. Size & Quality Checks
        if ($width < 300 || $height < 300) {
            return response()->json([
                'success' => false,
                'message' => 'Image resolution is too low. Minimum required resolution is 300x300 pixels.',
            ], 422);
        }

        return response()->json([
            'success' => true,
            'message' => 'Image validation passed.',
            'meta' => [
                'width' => $width,
                'height' => $height,
                'mime' => $mime,
                'size_bytes' => $file->getSize()
            ]
        ]);
    }

    /**
     * Delete image.
     */
    public function destroy(int $id): JsonResponse
    {
        $image = ProductImage::findOrFail($id);

        // Delete files from storage
        $this->deletePhysicalFiles($image->url);
        $this->deletePhysicalFiles($image->thumbnail_url);

        $image->delete();

        return response()->json([
            'success' => true,
            'message' => 'Image deleted successfully.'
        ]);
    }

    /**
     * Helper to parse and delete physical storage files by public URL.
     */
    protected function deletePhysicalFiles(?string $url): void
    {
        if (empty($url)) return;

        // E.g. URL: /storage/products/12/webp/hash.webp -> products/12/webp/hash.webp
        $path = preg_replace('/^\/?storage\//', '', parse_url($url, PHP_URL_PATH));
        if (Storage::disk('public')->exists($path)) {
            Storage::disk('public')->delete($path);
        }
    }

    /**
     * Resize and convert image using PHP GD.
     */
    protected function resizeAndConvertWebp(string $sourcePath, string $destinationPath, int $targetWidth, int $quality = 80): bool
    {
        if (!function_exists('imagecreatefromjpeg')) {
            // Fallback: GD extension is disabled in XAMPP php.ini. Copy file directly without resizing.
            return @copy($sourcePath, $destinationPath);
        }

        $info = getimagesize($sourcePath);
        if (!$info) return false;

        $mime = $info['mime'];
        $width = $info[0];
        $height = $info[1];

        switch ($mime) {
            case 'image/jpeg':
            case 'image/jpg':
                $image = @imagecreatefromjpeg($sourcePath);
                break;
            case 'image/png':
                $image = @imagecreatefrompng($sourcePath);
                if ($image) {
                    imagepalettetotruecolor($image);
                    imagealphablending($image, true);
                    imagesavealpha($image, true);
                }
                break;
            case 'image/webp':
                $image = @imagecreatefromwebp($sourcePath);
                break;
            default:
                return false;
        }

        if (!$image) return false;

        // Calculate aspect ratio
        $aspectRatio = $width / $height;
        if ($width > $targetWidth) {
            $newWidth = $targetWidth;
            $newHeight = intval($targetWidth / $aspectRatio);
        } else {
            $newWidth = $width;
            $newHeight = $height;
        }

        // Create canvas and preserve alpha values for transparent PNGs/WEBP
        $newImage = imagecreatetruecolor($newWidth, $newHeight);
        if ($newImage) {
            imagealphablending($newImage, false);
            imagesavealpha($newImage, true);
            imagecopyresampled($newImage, $image, 0, 0, 0, 0, $newWidth, $newHeight, $width, $height);
            $success = imagewebp($newImage, $destinationPath, $quality);
            imagedestroy($newImage);
        } else {
            $success = false;
        }

        imagedestroy($image);
        return $success;
    }
}
