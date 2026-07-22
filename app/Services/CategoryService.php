<?php

namespace App\Services;

use App\Repositories\CategoryRepositoryInterface;
use App\Models\Category;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Str;

class CategoryService
{
    protected $categoryRepository;

    public function __construct(CategoryRepositoryInterface $categoryRepository)
    {
        $this->categoryRepository = $categoryRepository;
    }

    public function getAllCategories(): Collection
    {
        return $this->categoryRepository->all();
    }

    public function getCategoryTree(): Collection
    {
        return $this->categoryRepository->getTree();
    }

    public function getCategoryById(int $id): ?Category
    {
        return $this->categoryRepository->find($id);
    }

    public function createCategory(array $data): Category
    {
        if (isset($data['image']) && $data['image'] instanceof \Illuminate\Http\UploadedFile) {
            $data['image'] = $this->processCategoryImage($data['image']);
        }

        if (empty($data['slug'])) {
            $data['slug'] = Str::slug($data['name']);
        } else {
            $data['slug'] = Str::slug($data['slug']);
        }

        // Handle unique slug constraint logic
        $data['slug'] = $this->makeSlugUnique($data['slug']);

        return $this->categoryRepository->create($data);
    }

    public function updateCategory(int $id, array $data): ?Category
    {
        if (isset($data['image']) && $data['image'] instanceof \Illuminate\Http\UploadedFile) {
            $data['image'] = $this->processCategoryImage($data['image'], $id);
        }

        if (!empty($data['name']) && empty($data['slug'])) {
            $data['slug'] = Str::slug($data['name']);
        } elseif (!empty($data['slug'])) {
            $data['slug'] = Str::slug($data['slug']);
        }

        if (!empty($data['slug'])) {
            $data['slug'] = $this->makeSlugUnique($data['slug'], $id);
        }

        return $this->categoryRepository->update($id, $data);
    }

    public function processCategoryImage(\Illuminate\Http\UploadedFile $file, ?int $categoryId = null): string
    {
        $folder = $categoryId ? "categories/{$categoryId}" : "categories";
        $originalDir = "{$folder}/original";
        $webpDir = "{$folder}/webp";

        \Illuminate\Support\Facades\Storage::disk('public')->makeDirectory($originalDir);
        \Illuminate\Support\Facades\Storage::disk('public')->makeDirectory($webpDir);

        $fileName = Str::uuid()->toString();
        $originalExt = $file->getClientOriginalExtension();
        $originalPath = $file->storeAs($originalDir, "{$fileName}.{$originalExt}", 'public');
        $sourcePath = \Illuminate\Support\Facades\Storage::disk('public')->path($originalPath);

        $webpPath = "{$folder}/webp/{$fileName}.webp";
        $fullWebpPath = \Illuminate\Support\Facades\Storage::disk('public')->path($webpPath);

        $success = $this->resizeAndConvertWebp($sourcePath, $fullWebpPath, 1000);

        if (!$success) {
            return '/storage/' . $originalPath;
        }

        return '/storage/' . $webpPath;
    }

    protected function resizeAndConvertWebp(string $sourcePath, string $destinationPath, int $targetWidth = 1000, int $quality = 80): bool
    {
        if (!function_exists('imagecreatefromjpeg')) {
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

        $aspectRatio = $width / $height;
        if ($width > $targetWidth) {
            $newWidth = $targetWidth;
            $newHeight = intval($targetWidth / $aspectRatio);
        } else {
            $newWidth = $width;
            $newHeight = $height;
        }

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

    public function deleteCategory(int $id): bool
    {
        // Add business validation: cannot delete category if it has subcategories
        $category = $this->categoryRepository->find($id);
        if ($category && $category->children()->count() > 0) {
            throw new \Exception("Cannot delete category with subcategories.", 409);
        }
        
        return $this->categoryRepository->delete($id);
    }

    protected function makeSlugUnique(string $slug, ?int $excludeId = null): string
    {
        $originalSlug = $slug;
        $count = 1;

        while (true) {
            $query = Category::where('slug', $slug);
            if ($excludeId !== null) {
                $query->where('id', '!=', $excludeId);
            }

            if (!$query->exists()) {
                break;
            }

            $slug = $originalSlug . '-' . $count;
            $count++;
        }

        return $slug;
    }
}
