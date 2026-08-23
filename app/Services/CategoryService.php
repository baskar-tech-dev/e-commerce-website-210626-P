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
        if (empty($data['slug'])) {
            $data['slug'] = Str::slug($data['name']);
        } else {
            $data['slug'] = Str::slug($data['slug']);
        }

        // Handle unique slug constraint logic
        $data['slug'] = $this->makeSlugUnique($data['slug']);

        $data = $this->processCategoryImage($data);

        return $this->categoryRepository->create($data);
    }

    public function updateCategory(int $id, array $data): ?Category
    {
        if (!empty($data['name']) && empty($data['slug'])) {
            $data['slug'] = Str::slug($data['name']);
        } elseif (!empty($data['slug'])) {
            $data['slug'] = Str::slug($data['slug']);
        }

        if (!empty($data['slug'])) {
            $data['slug'] = $this->makeSlugUnique($data['slug'], $id);
        }

        $data = $this->processCategoryImage($data);

        return $this->categoryRepository->update($id, $data);
    }

    /**
     * Move temp uploaded category images to permanent categories storage directory.
     */
    protected function processCategoryImage(array $data): array
    {
        $moveTemp = function (?string $url, string $subfolder) {
            if (empty($url) || !str_contains($url, '/temp/')) {
                return $url;
            }
            try {
                $pathOnly = parse_url($url, PHP_URL_PATH) ?? $url;
                $relPath = preg_replace('/^\/?storage\//', '', $pathOnly);
                if (\Illuminate\Support\Facades\Storage::disk('public')->exists($relPath)) {
                    $dir = "categories/{$subfolder}";
                    \Illuminate\Support\Facades\Storage::disk('public')->makeDirectory($dir);
                    $fileName = basename($relPath);
                    $newPath = "{$dir}/{$fileName}";
                    if (!@\Illuminate\Support\Facades\Storage::disk('public')->move($relPath, $newPath)) {
                        @\Illuminate\Support\Facades\Storage::disk('public')->copy($relPath, $newPath);
                        @\Illuminate\Support\Facades\Storage::disk('public')->delete($relPath);
                    }
                    return '/storage/' . $newPath;
                }
            } catch (\Throwable $e) {
                \Illuminate\Support\Facades\Log::warning("processCategoryImage move failed: " . $e->getMessage());
            }
            return $url;
        };

        if (isset($data['image'])) {
            $data['image'] = $moveTemp($data['image'], 'covers');
        }
        if (isset($data['size_chart_image'])) {
            $data['size_chart_image'] = $moveTemp($data['size_chart_image'], 'size_charts');
        }

        return $data;
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
            $query = Category::withTrashed()->where('slug', $slug);
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
