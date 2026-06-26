<?php

namespace App\Repositories;

use App\Models\Brand;
use Illuminate\Database\Eloquent\Collection;

interface BrandRepositoryInterface
{
    public function all(): Collection;
    public function find(int $id): ?Brand;
    public function findBySlug(string $slug): ?Brand;
    public function create(array $data): Brand;
    public function update(int $id, array $data): ?Brand;
    public function delete(int $id): bool;
}
