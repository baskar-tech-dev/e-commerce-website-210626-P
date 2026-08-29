<?php

namespace App\Repositories;

use App\Models\InventoryLedger;
use App\Models\ProductVariant;
use Illuminate\Pagination\LengthAwarePaginator;

interface InventoryRepositoryInterface
{
    /**
     * Get paginated variant stock items.
     */
    public function allVariants(array $filters = [], int $perPage = 15): LengthAwarePaginator;

    /**
     * Get paginated ledger movement history.
     */
    public function ledgerHistory(array $filters = [], int $perPage = 15): LengthAwarePaginator;

    /**
     * Find a variant with product details.
     */
    public function findVariant(int $variantId): ?ProductVariant;

    /**
     * Get stock overview KPI summary statistics.
     */
    public function stockOverviewStats(array $filters = []): array;

    /**
     * Get paginated product stock overview records.
     */
    public function stockOverview(array $filters = [], int $perPage = 24): LengthAwarePaginator;
}
