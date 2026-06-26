<?php

namespace App\Repositories;

use App\Models\Tag;
use Illuminate\Database\Eloquent\Collection;

class TagRepository implements TagRepositoryInterface
{
    public function all(): Collection
    {
        return Tag::orderBy('name')->get();
    }

    public function find(int $id): ?Tag
    {
        return Tag::find($id);
    }

    public function findBySlug(string $slug): ?Tag
    {
        return Tag::where('slug', $slug)->first();
    }

    public function create(array $data): Tag
    {
        return Tag::create($data);
    }

    public function update(int $id, array $data): ?Tag
    {
        $tag = Tag::find($id);
        if ($tag) {
            $tag->update($data);
            return $tag;
        }
        return null;
    }

    public function delete(int $id): bool
    {
        $tag = Tag::find($id);
        if ($tag) {
            return $tag->delete();
        }
        return false;
    }
}
