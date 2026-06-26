<?php

namespace App\Services;

use App\Repositories\BrandRepositoryInterface;
use App\Models\Brand;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Str;

class BrandService
{
    protected $brandRepository;

    public function __construct(BrandRepositoryInterface $brandRepository)
    {
        $this->brandRepository = $brandRepository;
    }

    public function getAllBrands(): Collection
    {
        return $this->brandRepository->all();
    }

    public function getBrandById(int $id): ?Brand
    {
        return $this->brandRepository->find($id);
    }

    public function createBrand(array $data): Brand
    {
        if (empty($data['slug'])) {
            $data['slug'] = Str::slug($data['name']);
        } else {
            $data['slug'] = Str::slug($data['slug']);
        }

        $data['slug'] = $this->makeSlugUnique($data['slug']);

        return $this->brandRepository->create($data);
    }

    public function updateBrand(int $id, array $data): ?Brand
    {
        if (!empty($data['name']) && empty($data['slug'])) {
            $data['slug'] = Str::slug($data['name']);
        } elseif (!empty($data['slug'])) {
            $data['slug'] = Str::slug($data['slug']);
        }

        if (!empty($data['slug'])) {
            $data['slug'] = $this->makeSlugUnique($data['slug'], $id);
        }

        return $this->brandRepository->update($id, $data);
    }

    public function deleteBrand(int $id): bool
    {
        return $this->brandRepository->delete($id);
    }

    protected function makeSlugUnique(string $slug, ?int $excludeId = null): string
    {
        $originalSlug = $slug;
        $count = 1;

        while (true) {
            $query = Brand::where('slug', $slug);
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
