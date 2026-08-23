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
     * Move temp uploaded category image to permanent categories storage directory.
     */
    protected function processCategoryImage(array $data): array
    {
        if (!empty($data['image']) && str_contains($data['image'], '/temp/')) {
            $relPath = preg_replace('/^\/?storage\//', '', parse_url($data['image'], PHP_URL_PATH));
            if (\Illuminate\Support\Facades\Storage::disk('public')->exists($relPath)) {
                \Illuminate\Support\Facades\Storage::disk('public')->makeDirectory('categories');
                $fileName = basename($relPath);
                $newPath = "categories/{$fileName}";
                if (!@\Illuminate\Support\Facades\Storage::disk('public')->move($relPath, $newPath)) {
                    @\Illuminate\Support\Facades\Storage::disk('public')->copy($relPath, $newPath);
                    @\Illuminate\Support\Facades\Storage::disk('public')->delete($relPath);
                }
                $data['image'] = '/storage/' . $newPath;
            }
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
