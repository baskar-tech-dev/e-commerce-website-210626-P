<?php

namespace App\Repositories;

use App\Models\PurchaseOrder;
use Illuminate\Pagination\LengthAwarePaginator;

class PurchaseOrderRepository implements PurchaseOrderRepositoryInterface
{
    /**
     * Get paginated purchase orders list.
     */
    public function all(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        $query = PurchaseOrder::query();

        if (isset($filters['status']) && $filters['status'] !== '') {
            $query->where('status', $filters['status']);
        }

        if (!empty($filters['supplier_name'])) {
            $query->where('supplier_name', 'like', "%{$filters['supplier_name']}%");
        }

        if (isset($filters['date_from']) && $filters['date_from'] !== '') {
            $query->where('created_at', '>=', $filters['date_from'] . ' 00:00:00');
        }

        if (isset($filters['date_to']) && $filters['date_to'] !== '') {
            $query->where('created_at', '<=', $filters['date_to'] . ' 23:59:59');
        }

        return $query->orderBy('created_at', 'desc')
            ->paginate($perPage);
    }

    /**
     * Find purchase order by ID with details.
     */
    public function find(int $id): ?PurchaseOrder
    {
        return PurchaseOrder::with(['items.variant.product', 'creator'])->find($id);
    }

    /**
     * Create a purchase order.
     */
    public function create(array $data): PurchaseOrder
    {
        return PurchaseOrder::create($data);
    }

    /**
     * Update a purchase order.
     */
    public function update(int $id, array $data): ?PurchaseOrder
    {
        $po = PurchaseOrder::find($id);
        if ($po) {
            $po->update($data);
            return $po;
        }
        return null;
    }

    /**
     * Delete a purchase order.
     */
    public function delete(int $id): bool
    {
        $po = PurchaseOrder::find($id);
        if ($po) {
            return $po->delete();
        }
        return false;
    }
}
