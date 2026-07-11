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
}
