<?php

namespace App\Repositories;

use App\Models\PurchaseOrder;
use Illuminate\Pagination\LengthAwarePaginator;

interface PurchaseOrderRepositoryInterface
{
    /**
     * Get paginated purchase orders list.
     */
    public function all(array $filters = [], int $perPage = 15): LengthAwarePaginator;

    /**
     * Find purchase order by ID with details.
     */
    public function find(int $id): ?PurchaseOrder;

    /**
     * Create a purchase order.
     */
    public function create(array $data): PurchaseOrder;

    /**
     * Update a purchase order.
     */
    public function update(int $id, array $data): ?PurchaseOrder;

    /**
     * Delete a purchase order.
     */
    public function delete(int $id): bool;
}
