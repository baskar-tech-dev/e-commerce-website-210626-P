<?php

namespace App\Repositories;

use App\Models\Product;
use App\Models\ProductVariant;
use Illuminate\Pagination\LengthAwarePaginator;

interface ProductRepositoryInterface
{
    /**
     * Get products list with paginated filters.
     */
    public function all(array $filters = [], int $perPage = 15): LengthAwarePaginator;

    /**
     * Find product by ID.
     */
    public function find(int $id): ?Product;

    /**
     * Find product by slug.
     */
    public function findBySlug(string $slug): ?Product;

    /**
     * Create product.
     */
    public function create(array $data): Product;

    /**
     * Update product.
     */
    public function update(int $id, array $data): ?Product;

    /**
     * Delete product.
     */
    public function delete(int $id): bool;

    /**
     * Find variant by SKU.
     */
    public function findVariantBySku(string $sku): ?ProductVariant;
}
