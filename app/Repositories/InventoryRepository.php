<?php

namespace App\Repositories;

use App\Models\InventoryLedger;
use App\Models\ProductVariant;
use Illuminate\Pagination\LengthAwarePaginator;

class InventoryRepository implements InventoryRepositoryInterface
{
    /**
     * Get paginated variant stock items.
     */
    public function allVariants(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        $query = ProductVariant::with(['product.category']);

        if (isset($filters['category_id']) && $filters['category_id'] !== '') {
            $query->whereHas('product', function ($q) use ($filters) {
                $q->where('category_id', $filters['category_id']);
            });
        }

        if (!empty($filters['search'])) {
            $search = $filters['search'];
            $query->where(function ($q) use ($search) {
                $q->where('sku', 'like', "%{$search}%")
                  ->orWhere('barcode', 'like', "%{$search}%")
                  ->orWhereHas('product', function ($sub) use ($search) {
                      $sub->where('name', 'like', "%{$search}%");
                  });
            });
        }

        if (isset($filters['status']) && $filters['status'] !== '') {
            switch ($filters['status']) {
                case 'ok':
                    $query->whereColumn('stock_quantity', '>', 'low_stock_threshold');
                    break;
                case 'low':
                    $query->where('stock_quantity', '>', 0)
                          ->whereColumn('stock_quantity', '<=', 'low_stock_threshold');
                    break;
                case 'out_of_stock':
                    $query->where('stock_quantity', '=', 0);
                    break;
            }
        }

        return $query->orderBy('stock_quantity', 'asc')
            ->orderBy('sku', 'asc')
            ->paginate($perPage);
    }

    /**
     * Get paginated ledger movement history.
     */
    public function ledgerHistory(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        $query = InventoryLedger::with(['variant.product', 'creator']);

        if (isset($filters['product_variant_id']) && $filters['product_variant_id'] !== '') {
            $query->where('product_variant_id', $filters['product_variant_id']);
        }

        if (isset($filters['type']) && $filters['type'] !== '') {
            $query->where('type', $filters['type']);
        }

        if (isset($filters['direction']) && $filters['direction'] !== '') {
            $query->where('direction', $filters['direction']);
        }

        if (isset($filters['date_from']) && $filters['date_from'] !== '') {
            $query->where('created_at', '>=', $filters['date_from'] . ' 00:00:00');
        }

        if (isset($filters['date_to']) && $filters['date_to'] !== '') {
            $query->where('created_at', '<=', $filters['date_to'] . ' 23:59:59');
        }

        return $query->orderBy('created_at', 'desc')
            ->orderBy('id', 'desc')
            ->paginate($perPage);
    }

    /**
     * Find a variant with product details.
     */
    public function findVariant(int $variantId): ?ProductVariant
    {
        return ProductVariant::with(['product.category'])->find($variantId);
    }
}
