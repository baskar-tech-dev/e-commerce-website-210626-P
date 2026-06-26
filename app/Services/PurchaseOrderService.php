<?php

namespace App\Services;

use App\Repositories\PurchaseOrderRepositoryInterface;
use App\Models\PurchaseOrder;
use App\Models\PurchaseOrderItem;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Exception;

class PurchaseOrderService
{
    protected $purchaseOrderRepository;
    protected $inventoryService;

    public function __construct(
        PurchaseOrderRepositoryInterface $purchaseOrderRepository,
        InventoryService $inventoryService
    ) {
        $this->purchaseOrderRepository = $purchaseOrderRepository;
        $this->inventoryService = $inventoryService;
    }

    /**
     * Get paginated purchase orders list.
     */
    public function getPaginatedPurchaseOrders(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        return $this->purchaseOrderRepository->all($filters, $perPage);
    }

    /**
     * Get purchase order by ID.
     */
    public function getPurchaseOrderById(int $id): ?PurchaseOrder
    {
        return $this->purchaseOrderRepository->find($id);
    }

    /**
     * Create a purchase order in draft.
     */
    public function createPurchaseOrder(array $data, ?int $userId = null): PurchaseOrder
    {
        return DB::transaction(function () use ($data, $userId) {
            $poData = [
                'supplier_name' => $data['supplier_name'],
                'supplier_contact' => $data['supplier_contact'] ?? null,
                'notes' => $data['notes'] ?? null,
                'status' => 'draft',
                'created_by' => $userId,
                'total_amount' => 0.00,
            ];

            $po = $this->purchaseOrderRepository->create($poData);

            $totalAmount = 0.00;

            if (isset($data['items']) && is_array($data['items'])) {
                foreach ($data['items'] as $item) {
                    $qty = (int)$item['quantity_ordered'];
                    $cost = (float)$item['unit_cost'];
                    $lineTotal = $qty * $cost;
                    $totalAmount += $lineTotal;

                    $po->items()->create([
                        'product_variant_id' => $item['product_variant_id'],
                        'quantity_ordered' => $qty,
                        'quantity_received' => 0,
                        'unit_cost' => $cost,
                        'total_cost' => $lineTotal,
                    ]);
                }
            }

            $po->total_amount = $totalAmount;
            $po->save();

            return $po->load(['items.variant.product', 'creator']);
        });
    }

    /**
     * Update a draft purchase order.
     */
    public function updatePurchaseOrder(int $id, array $data): ?PurchaseOrder
    {
        return DB::transaction(function () use ($id, $data) {
            $po = $this->purchaseOrderRepository->find($id);
            if (!$po) {
                return null;
            }

            if ($po->status !== 'draft') {
                throw new Exception("Cannot update a purchase order that is already '{$po->status}'. Only draft orders can be modified.");
            }

            $po->update([
                'supplier_name' => $data['supplier_name'] ?? $po->supplier_name,
                'supplier_contact' => $data['supplier_contact'] ?? $po->supplier_contact,
                'notes' => $data['notes'] ?? $po->notes,
                'status' => $data['status'] ?? $po->status, // Support submitting PO
            ]);

            // If status is updated to ordered, set ordered_at
            if ($po->status === 'ordered' && empty($po->ordered_at)) {
                $po->ordered_at = now();
            }

            if (isset($data['items']) && is_array($data['items'])) {
                // Delete old items
                $po->items()->delete();

                $totalAmount = 0.00;
                foreach ($data['items'] as $item) {
                    $qty = (int)$item['quantity_ordered'];
                    $cost = (float)$item['unit_cost'];
                    $lineTotal = $qty * $cost;
                    $totalAmount += $lineTotal;

                    $po->items()->create([
                        'product_variant_id' => $item['product_variant_id'],
                        'quantity_ordered' => $qty,
                        'quantity_received' => $item['quantity_received'] ?? 0,
                        'unit_cost' => $cost,
                        'total_cost' => $lineTotal,
                    ]);
                }
                $po->total_amount = $totalAmount;
                $po->save();
            }

            return $po->load(['items.variant.product', 'creator']);
        });
    }

    /**
     * Delete a purchase order (draft only).
     */
    public function deletePurchaseOrder(int $id): bool
    {
        return DB::transaction(function () use ($id) {
            $po = $this->purchaseOrderRepository->find($id);
            if (!$po) {
                return false;
            }

            if ($po->status !== 'draft') {
                throw new Exception("Cannot delete a purchase order that is already '{$po->status}'. Only draft orders can be deleted.");
            }

            $po->items()->delete();
            return $this->purchaseOrderRepository->delete($id);
        });
    }

    /**
     * Receive item stock under an ordered PO.
     */
    public function receiveStock(int $id, array $data, ?int $userId = null): PurchaseOrder
    {
        return DB::transaction(function () use ($id, $data, $userId) {
            $po = $this->purchaseOrderRepository->find($id);
            if (!$po) {
                throw new Exception("Purchase order with ID {$id} not found.");
            }

            if (in_array($po->status, ['received', 'cancelled'])) {
                throw new Exception("Cannot receive stock for purchase order that is '{$po->status}'.");
            }

            // Lock PO items for update to prevent concurrent updates
            $itemsData = $data['items'] ?? [];
            
            foreach ($itemsData as $receiveItem) {
                $itemId = $receiveItem['item_id'];
                $newQtyReceived = (int)$receiveItem['quantity_received'];

                if ($newQtyReceived <= 0) {
                    continue; // Skip items with no new received quantities
                }

                $poItem = $po->items()->find($itemId);
                if (!$poItem) {
                    throw new Exception("Purchase order item with ID {$itemId} not found under this PO.");
                }

                // Increment received quantity
                $poItem->quantity_received += $newQtyReceived;
                $poItem->save();

                // Post IN transaction in inventory ledger
                $this->inventoryService->postLedgerEntry(
                    $poItem->product_variant_id,
                    'PURCHASE',
                    'IN',
                    $newQtyReceived,
                    $poItem->unit_cost,
                    PurchaseOrder::class,
                    $po->id,
                    "Received under Purchase Order #{$po->po_number}",
                    $userId
                );
            }

            // Recalculate status of PO
            $po->load('items');
            $allFullyReceived = true;
            $anyReceived = false;

            foreach ($po->items as $item) {
                if ($item->quantity_received < $item->quantity_ordered) {
                    $allFullyReceived = false;
                }
                if ($item->quantity_received > 0) {
                    $anyReceived = true;
                }
            }

            if ($allFullyReceived) {
                $po->status = 'received';
                $po->received_at = now();
            } elseif ($anyReceived) {
                $po->status = 'partial';
                $po->received_at = now();
            }

            $po->save();

            return $po->load(['items.variant.product', 'creator']);
        });
    }
}
