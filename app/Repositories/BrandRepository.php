<?php

namespace App\Repositories;

use App\Models\Brand;
use Illuminate\Database\Eloquent\Collection;

class BrandRepository implements BrandRepositoryInterface
{
    public function all(): Collection
    {
        return Brand::orderBy('sort_order')->get();
    }

    public function find(int $id): ?Brand
    {
        return Brand::find($id);
    }

    public function findBySlug(string $slug): ?Brand
    {
        return Brand::where('slug', $slug)->first();
    }

    public function create(array $data): Brand
    {
        return Brand::create($data);
    }

    public function update(int $id, array $data): ?Brand
    {
        $brand = Brand::find($id);
        if ($brand) {
            $brand->update($data);
            return $brand;
        }
        return null;
    }

    public function delete(int $id): bool
    {
        $brand = Brand::find($id);
        if ($brand) {
            return $brand->delete();
        }
        return false;
    }
}
