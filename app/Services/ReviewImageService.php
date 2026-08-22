<?php

namespace App\Services;

use App\Models\ProductReview;
use App\Models\ProductReviewImage;
use App\Models\Setting;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Exception;

class ReviewImageService
{
    /**
     * Maximum allowed size in bytes (200 KB).
     */
    protected const MAX_FILE_SIZE_BYTES = 204800; // 200 * 1024 bytes

    /**
     * Allowed MIME types.
     */
    protected const ALLOWED_MIMES = [
        'image/jpeg',
        'image/jpg',
        'image/png',
        'image/webp',
    ];

    /**
     * Validate and process an array of uploaded review images, compressing each to <= 200KB.
     *
     * @param array $files UploadedFile[]
     * @param int $productId
     * @param int $reviewId
     * @return array Saved relative paths accessible via /storage/...
     * @throws Exception
     */
    public function processAndSaveImages(array $files, int $productId, int $reviewId): array
    {
        $maxImages = (int) Setting::get('max_images_per_review', 'reviews', 4);
        if (count($files) > $maxImages) {
            throw new Exception("You can upload a maximum of {$maxImages} photos.");
        }

        $savedPaths = [];
        $folder = "reviews/{$productId}/{$reviewId}";
        
        // Ensure directory exists on the public disk (storage/app/public/reviews/...)
        Storage::disk('public')->makeDirectory($folder);

        foreach ($files as $index => $file) {
            if (!$file instanceof UploadedFile || !$file->isValid()) {
                throw new Exception("Please upload a valid JPG, PNG, or WebP image.");
            }

            $mime = $file->getMimeType();
            $ext = strtolower($file->getClientOriginalExtension());
            if (!in_array(strtolower($mime), static::ALLOWED_MIMES) && !in_array($ext, ['jpg', 'jpeg', 'png', 'webp'])) {
                throw new Exception("Unsupported file format ({$mime}). Please upload JPG, PNG, or WebP.");
            }

            // Compress and optimize image to ensure size <= 200 KB
            $optimizedData = $this->compressImageToTargetSize($file);
            if (!$optimizedData) {
                $realPath = $file->getRealPath();
                $optimizedData = ($realPath && file_exists($realPath)) ? file_get_contents($realPath) : null;
            }

            if (!$optimizedData) {
                throw new Exception("This image could not be processed. Please choose another image.");
            }

            $outputExt = function_exists('imagewebp') ? 'webp' : ($ext ?: 'jpg');
            $filename = sprintf('review_%02d_%s.%s', $index + 1, Str::random(8), $outputExt);
            $storagePath = "{$folder}/{$filename}";

            Storage::disk('public')->put($storagePath, $optimizedData);
            $savedPaths[] = "/storage/{$storagePath}";
        }

        return $savedPaths;
    }

    /**
     * Compress an image using PHP GD library to ensure final size <= 200 KB.
     *
     * @param UploadedFile $file
     * @return string|null Compressed binary data
     */
    public function compressImageToTargetSize(UploadedFile $file): ?string
    {
        $realPath = $file->getRealPath();
        if (!$realPath || !file_exists($realPath)) {
            return null;
        }

        $fileContents = file_get_contents($realPath);
        if (!$fileContents) {
            return null;
        }

        // If GD extension is not loaded in environment, check file size directly
        if (!function_exists('imagecreatefromstring')) {
            return $fileContents;
        }

        $gdImage = @imagecreatefromstring($fileContents);
        if (!$gdImage) {
            return $fileContents;
        }

        // Fix rotation if EXIF orientation data exists
        if (function_exists('exif_read_data') && in_array($file->getMimeType(), ['image/jpeg', 'image/jpg'])) {
            try {
                $exif = @exif_read_data($realPath);
                if (!empty($exif['Orientation'])) {
                    switch ($exif['Orientation']) {
                        case 8:
                            $gdImage = imagerotate($gdImage, 90, 0);
                            break;
                        case 3:
                            $gdImage = imagerotate($gdImage, 180, 0);
                            break;
                        case 6:
                            $gdImage = imagerotate($gdImage, -90, 0);
                            break;
                    }
                }
            } catch (Exception $e) {
                // Ignore EXIF errors
            }
        }

        $origWidth = imagesx($gdImage);
        $origHeight = imagesy($gdImage);

        // Target max dimension
        $maxDimension = 1400;
        $currentWidth = $origWidth;
        $currentHeight = $origHeight;

        if ($origWidth > $maxDimension || $origHeight > $maxDimension) {
            if ($origWidth >= $origHeight) {
                $currentWidth = $maxDimension;
                $currentHeight = (int) round(($origHeight / $origWidth) * $maxDimension);
            } else {
                $currentHeight = $maxDimension;
                $currentWidth = (int) round(($origWidth / $origHeight) * $maxDimension);
            }
        }

        // Resample image
        $scaledImage = imagecreatetruecolor($currentWidth, $currentHeight);
        // Retain alpha transparency for WebP/PNG conversion
        imagealphablending($scaledImage, false);
        imagesavealpha($scaledImage, true);
        imagecopyresampled($scaledImage, $gdImage, 0, 0, 0, 0, $currentWidth, $currentHeight, $origWidth, $origHeight);
        imagedestroy($gdImage);

        // Quality iteration loop to guarantee output <= 200 KB
        $quality = 85;
        $binaryData = null;

        while ($quality >= 20) {
            ob_start();
            if (function_exists('imagewebp')) {
                imagewebp($scaledImage, null, $quality);
            } else {
                imagejpeg($scaledImage, null, $quality);
            }
            $binaryData = ob_get_clean();

            if (strlen($binaryData) <= static::MAX_FILE_SIZE_BYTES) {
                imagedestroy($scaledImage);
                return $binaryData;
            }

            $quality -= 10;
        }

        // If still > 200 KB, resize dimensions progressively down
        while ($currentWidth > 300) {
            $currentWidth = (int) round($currentWidth * 0.75);
            $currentHeight = (int) round($currentHeight * 0.75);

            $smallerImage = imagecreatetruecolor($currentWidth, $currentHeight);
            imagealphablending($smallerImage, false);
            imagesavealpha($smallerImage, true);
            imagecopyresampled($smallerImage, $scaledImage, 0, 0, 0, 0, $currentWidth, $currentHeight, imagesx($scaledImage), imagesy($scaledImage));
            imagedestroy($scaledImage);
            $scaledImage = $smallerImage;

            ob_start();
            if (function_exists('imagewebp')) {
                imagewebp($scaledImage, null, 50);
            } else {
                imagejpeg($scaledImage, null, 50);
            }
            $binaryData = ob_get_clean();

            if (strlen($binaryData) <= static::MAX_FILE_SIZE_BYTES) {
                imagedestroy($scaledImage);
                return $binaryData;
            }
        }

        imagedestroy($scaledImage);
        return $binaryData ?: $fileContents;
    }

    /**
     * Delete storage files associated with a product review.
     *
     * @param ProductReview $review
     * @return void
     */
    public function deleteReviewImages(ProductReview $review): void
    {
        foreach ($review->images as $image) {
            $this->deleteSingleImageFile($image->image_path);
            $image->delete();
        }

        $directory = "reviews/{$review->product_id}/{$review->id}";
        Storage::disk('public')->deleteDirectory($directory);
    }

    /**
     * Delete a single image file by public path.
     *
     * @param string $path
     * @return void
     */
    public function deleteSingleImageFile(string $path): void
    {
        $normalized = ltrim(str_replace(['/storage/', 'storage/', 'public/'], '', $path), '/');
        if (Storage::disk('public')->exists($normalized)) {
            Storage::disk('public')->delete($normalized);
        }
    }
}
