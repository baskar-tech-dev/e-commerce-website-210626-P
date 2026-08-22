<?php

namespace App\Services;

use App\Repositories\InventoryRepositoryInterface;
use App\Models\InventoryLedger;
use App\Models\ProductVariant;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Exception;

class InventoryService
{
    protected $inventoryRepository;

    public function __construct(InventoryRepositoryInterface $inventoryRepository)
    {
        $this->inventoryRepository = $inventoryRepository;
    }

    /**
     * Get paginated variants stock.
     */
    public function getPaginatedVariants(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        return $this->inventoryRepository->allVariants($filters, $perPage);
    }

    /**
     * Get paginated ledger movement history.
     */
    public function getPaginatedLedger(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        return $this->inventoryRepository->ledgerHistory($filters, $perPage);
    }

    /**
     * Execute a stock adjustment (IN, OUT, or DAMAGE).
     */
    public function adjustStock(int $variantId, string $type, int $quantity, string $reason, ?string $notes = null, ?int $userId = null): InventoryLedger
    {
        return DB::transaction(function () use ($variantId, $type, $quantity, $reason, $notes, $userId) {
            $direction = 'OUT';
            $ledgerType = 'ADJUSTMENT';

            if ($type === 'adjustment_in') {
                $direction = 'IN';
                $ledgerType = 'ADJUSTMENT';
            } elseif ($type === 'adjustment_out') {
                $direction = 'OUT';
                $ledgerType = 'ADJUSTMENT';
            } elseif ($type === 'damage') {
                $direction = 'OUT';
                $ledgerType = 'DAMAGE';
            } else {
                throw new Exception("Invalid adjustment type: {$type}");
            }

            $fullNotes = trim("Reason: {$reason}. " . ($notes ?? ''));

            return $this->postLedgerEntry(
                $variantId,
                $ledgerType,
                $direction,
                $quantity,
                null,
                null,
                null,
                $fullNotes,
                $userId
            );
        });
    }

    /**
     * strict double-entry posting ledger transaction.
     */
    public function postLedgerEntry(
        int $variantId,
        string $type,
        string $direction,
        int $quantity,
        ?float $unitCost = null,
        ?string $refType = null,
        ?int $refId = null,
        ?string $notes = null,
        ?int $userId = null
    ): InventoryLedger {
        if ($quantity <= 0) {
            throw new Exception("Ledger quantity must be greater than zero.");
        }

        return DB::transaction(function () use ($variantId, $type, $direction, $quantity, $unitCost, $refType, $refId, $notes, $userId) {
            // Lock variant row for update to prevent concurrent stock calculation bugs
            $variant = ProductVariant::lockForUpdate()->find($variantId);
            if (!$variant) {
                throw new Exception("Product variant with ID {$variantId} not found.");
            }

            $stockBefore = $variant->stock_quantity;
            $stockAfter = $stockBefore;

            if ($direction === 'IN') {
                $stockAfter = $stockBefore + $quantity;
            } elseif ($direction === 'OUT') {
                $stockAfter = $stockBefore - $quantity;
            } else {
                throw new Exception("Invalid ledger direction: {$direction}");
            }

            if ($stockAfter < 0) {
                throw new Exception("Operation failed: Insufficient stock for SKU '{$variant->sku}'. Current: {$stockBefore}, Requested: -{$quantity}.");
            }

            // Update variant stock
            $variant->stock_quantity = $stockAfter;
            $variant->save();

            // Record entry in ledger history
            return InventoryLedger::create([
                'product_variant_id' => $variantId,
                'type' => $type,
                'direction' => $direction,
                'quantity' => $quantity,
                'unit_cost' => $unitCost,
                'reference_type' => $refType,
                'reference_id' => $refId,
                'stock_before' => $stockBefore,
                'stock_after' => $stockAfter,
                'notes' => $notes,
                'created_by' => $userId,
            ]);
        });
    }

    /**
     * Reserve stock for a product variant (moves stock_quantity to reserved_quantity).
     */
    public function reserveStock(int $variantId, int $quantity, int $orderId, ?int $userId = null): void
    {
        if ($quantity <= 0) {
            throw new Exception("Reservation quantity must be greater than zero.");
        }

        DB::transaction(function () use ($variantId, $quantity, $orderId, $userId) {
            $variant = ProductVariant::lockForUpdate()->find($variantId);
            if (!$variant) {
                throw new Exception("Product variant with ID {$variantId} not found.");
            }

            $stockBefore = $variant->stock_quantity;
            if ($stockBefore < $quantity) {
                throw new Exception("Operation failed: Insufficient stock to reserve for SKU '{$variant->sku}'. Current: {$stockBefore}, Requested: {$quantity}.");
            }

            $variant->stock_quantity = $stockBefore - $quantity;
            $variant->reserved_quantity += $quantity;
            $variant->save();

            // Record entry in ledger history
            InventoryLedger::create([
                'product_variant_id' => $variantId,
                'type' => 'RESERVE',
                'direction' => 'OUT',
                'quantity' => $quantity,
                'reference_type' => 'Order',
                'reference_id' => $orderId,
                'stock_before' => $stockBefore,
                'stock_after' => $variant->stock_quantity,
                'notes' => "Stock reserved for order ID #{$orderId}",
                'created_by' => $userId,
            ]);
        });
    }

    /**
     * Commit reserved stock (reduces reserved_quantity without changing stock_quantity).
     */
    public function commitReservedStock(int $variantId, int $quantity, int $orderId, ?int $userId = null): void
    {
        if ($quantity <= 0) {
            throw new Exception("Commit quantity must be greater than zero.");
        }

        DB::transaction(function () use ($variantId, $quantity, $orderId, $userId) {
            $variant = ProductVariant::lockForUpdate()->find($variantId);
            if (!$variant) {
                throw new Exception("Product variant with ID {$variantId} not found.");
            }

            if ($variant->reserved_quantity < $quantity) {
                throw new Exception("Operation failed: Insufficient reserved stock to commit for SKU '{$variant->sku}'. Reserved: {$variant->reserved_quantity}, Requested: {$quantity}.");
            }

            $variant->reserved_quantity -= $quantity;
            $variant->save();

            // Record entry in ledger history
            InventoryLedger::create([
                'product_variant_id' => $variantId,
                'type' => 'SALE',
                'direction' => 'OUT',
                'quantity' => $quantity,
                'reference_type' => 'Order',
                'reference_id' => $orderId,
                'stock_before' => $variant->stock_quantity + $quantity,
                'stock_after' => $variant->stock_quantity,
                'notes' => "Reserved stock committed/sold for order ID #{$orderId}",
                'created_by' => $userId,
            ]);
        });
    }

    /**
     * Release reserved stock (moves reserved_quantity back to stock_quantity).
     */
    public function releaseReservedStock(int $variantId, int $quantity, int $orderId, ?int $userId = null): void
    {
        if ($quantity <= 0) {
            throw new Exception("Release quantity must be greater than zero.");
        }

        DB::transaction(function () use ($variantId, $quantity, $orderId, $userId) {
            $variant = ProductVariant::lockForUpdate()->find($variantId);
            if (!$variant) {
                throw new Exception("Product variant with ID {$variantId} not found.");
            }

            if ($variant->reserved_quantity < $quantity) {
                throw new Exception("Operation failed: Insufficient reserved stock to release for SKU '{$variant->sku}'. Reserved: {$variant->reserved_quantity}, Requested: {$quantity}.");
            }

            $stockBefore = $variant->stock_quantity;
            $variant->reserved_quantity -= $quantity;
            $variant->stock_quantity = $stockBefore + $quantity;
            $variant->save();

            // Record entry in ledger history
            InventoryLedger::create([
                'product_variant_id' => $variantId,
                'type' => 'RELEASE',
                'direction' => 'IN',
                'quantity' => $quantity,
                'reference_type' => 'Order',
                'reference_id' => $orderId,
                'stock_before' => $stockBefore,
                'stock_after' => $variant->stock_quantity,
                'notes' => "Reserved stock released for order ID #{$orderId}",
                'created_by' => $userId,
            ]);
        });
    }

    /**
     * Get structured Color x Size stock matrix for a product.
     */
    public function getProductStockMatrix(int $productId): array
    {
        $product = \App\Models\Product::with(['category:id,name', 'images', 'variants'])->find($productId);
        if (!$product) {
            throw new Exception("Product with ID {$productId} not found.");
        }

        $variants = $product->variants;
        $colorMap = [];
        $sizes = [];
        $matrix = [];

        // Preload Color master for accurate hex codes
        $masterColors = \App\Models\Color::pluck('code', 'name')->toArray();

        foreach ($variants as $v) {
            $color = trim($v->color ?: 'Standard');
            $size = trim($v->size ?: 'One Size');

            if (!isset($colorMap[$color])) {
                $code = $v->color_code ?: ($masterColors[$color] ?? '#4A0E2E');
                $colorMap[$color] = [
                    'name' => $color,
                    'code' => $code,
                ];
            }

            if (!in_array($size, $sizes)) {
                $sizes[] = $size;
            }

            if (!isset($matrix[$color])) {
                $matrix[$color] = [];
            }

            $matrix[$color][$size] = [
                'variant_id' => $v->id,
                'sku' => $v->sku,
                'color' => $color,
                'color_code' => $colorMap[$color]['code'],
                'size' => $size,
                'stock_quantity' => (int) $v->stock_quantity,
                'reserved_quantity' => (int) $v->reserved_quantity,
                'low_stock_threshold' => (int) $v->low_stock_threshold,
            ];
        }

        $primaryImage = $product->images->firstWhere('is_primary', true) ?: $product->images->first();

        return [
            'product' => [
                'id' => $product->id,
                'name' => $product->name,
                'slug' => $product->slug,
                'category_name' => $product->category?->name ?? 'Uncategorized',
                'primary_image_url' => $primaryImage?->url ?? $primaryImage?->thumbnail_url ?? null,
                'total_stock' => (int) $variants->sum('stock_quantity'),
                'total_reserved' => (int) $variants->sum('reserved_quantity'),
                'variants_count' => $variants->count(),
            ],
            'colors' => array_values($colorMap),
            'sizes' => $sizes,
            'variants' => $variants,
            'matrix' => $matrix,
        ];
    }

    /**
     * Bulk update stock across matrix cells for a product.
     *
     * @param int $productId
     * @param string $mode 'set' | 'add' | 'subtract'
     * @param string $reason
     * @param string|null $notes
     * @param array $items Array of ['variant_id' => int, 'quantity' => int]
     * @param int|null $userId
     * @return array
     * @throws Exception
     */
    public function bulkUpdateMatrixStock(
        int $productId,
        string $mode,
        string $reason,
        ?string $notes,
        array $items,
        ?int $userId = null
    ): array {
        if (!in_array($mode, ['set', 'add', 'subtract'])) {
            throw new Exception("Invalid update mode: {$mode}. Must be set, add, or subtract.");
        }

        return DB::transaction(function () use ($productId, $mode, $reason, $notes, $items, $userId) {
            $product = \App\Models\Product::find($productId);
            if (!$product) {
                throw new Exception("Product #{$productId} not found.");
            }

            $updatedCount = 0;
            $totalDelta = 0;
            $updatedVariants = [];

            foreach ($items as $item) {
                $variantId = (int) ($item['variant_id'] ?? 0);
                if (!$variantId) continue;

                $qty = (int) ($item['quantity'] ?? 0);

                $variant = ProductVariant::lockForUpdate()->where('product_id', $productId)->find($variantId);
                if (!$variant) continue;

                $stockBefore = (int) $variant->stock_quantity;
                $stockAfter = $stockBefore;
                $delta = 0;
                $direction = 'IN';

                if ($mode === 'set') {
                    $targetStock = max(0, $qty);
                    $delta = $targetStock - $stockBefore;
                    $stockAfter = $targetStock;
                    $direction = $delta >= 0 ? 'IN' : 'OUT';
                } elseif ($mode === 'add') {
                    if ($qty <= 0) continue;
                    $delta = $qty;
                    $stockAfter = $stockBefore + $qty;
                    $direction = 'IN';
                } elseif ($mode === 'subtract') {
                    if ($qty <= 0) continue;
                    $delta = -$qty;
                    $stockAfter = max(0, $stockBefore - $qty);
                    $direction = 'OUT';
                }

                if ($stockBefore === $stockAfter && $mode === 'set') {
                    // No change in quantity
                    continue;
                }

                // Update variant
                $variant->stock_quantity = $stockAfter;
                $variant->save();

                // Post audit ledger entry
                $auditNote = trim("Bulk Matrix ({$mode}): {$reason}. " . ($notes ?? ''));
                InventoryLedger::create([
                    'product_variant_id' => $variant->id,
                    'type' => 'ADJUSTMENT',
                    'direction' => $direction,
                    'quantity' => abs($delta),
                    'reference_type' => 'BULK_STOCK_ENTRY',
                    'reference_id' => $productId,
                    'stock_before' => $stockBefore,
                    'stock_after' => $stockAfter,
                    'notes' => $auditNote,
                    'created_by' => $userId,
                ]);

                $updatedCount++;
                $totalDelta += $delta;
                $updatedVariants[] = [
                    'id' => $variant->id,
                    'sku' => $variant->sku,
                    'color' => $variant->color,
                    'size' => $variant->size,
                    'stock_before' => $stockBefore,
                    'stock_after' => $stockAfter,
                    'delta' => $delta,
                ];
            }

            // Recalculate total product stock
            $newTotalStock = (int) ProductVariant::where('product_id', $productId)->sum('stock_quantity');

            return [
                'success' => true,
                'updated_count' => $updatedCount,
                'total_delta' => $totalDelta,
                'new_total_stock' => $newTotalStock,
                'updated_variants' => $updatedVariants,
            ];
        });
    }
}
