<?php

namespace App\Repositories;

use App\Models\Tag;
use Illuminate\Database\Eloquent\Collection;

interface TagRepositoryInterface
{
    public function all(): Collection;
    public function find(int $id): ?Tag;
    public function findBySlug(string $slug): ?Tag;
    public function create(array $data): Tag;
    public function update(int $id, array $data): ?Tag;
    public function delete(int $id): bool;
}
