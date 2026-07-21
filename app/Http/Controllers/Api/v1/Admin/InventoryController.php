<?php

namespace App\Http\Controllers\Api\v1\Admin;

use App\Http\Controllers\Controller;
use App\Services\InventoryService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Exception;

class InventoryController extends Controller
{
    protected $inventoryService;

    public function __construct(InventoryService $inventoryService)
    {
        $this->inventoryService = $inventoryService;
    }

    /**
     * Display listing of variants and their stock status.
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $filters = $request->only(['search', 'status', 'category_id']);
            $perPage = $request->input('per_page', 15);
            
            $variants = $this->inventoryService->getPaginatedVariants($filters, $perPage);

            return response()->json([
                'success' => true,
                'message' => 'Inventory stocks retrieved successfully',
                'data' => $variants->items(),
                'meta' => [
                    'current_page' => $variants->currentPage(),
                    'last_page' => $variants->lastPage(),
                    'per_page' => $variants->perPage(),
                    'total' => $variants->total(),
                ]
            ]);
        } catch (Exception $e) {
            Log::error('InventoryController@index failed: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve stock levels',
                'error_code' => 'SERVER_ERROR'
            ], 500);
        }
    }

    /**
     * Post a manual stock adjustment (IN, OUT, or DAMAGE).
     */
    public function adjust(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'product_variant_id' => 'required|exists:product_variants,id',
            'type' => 'required|string|in:adjustment_in,adjustment_out,damage',
            'quantity' => 'required|integer|min:1',
            'reason' => 'required|string|max:500',
            'notes' => 'nullable|string|max:500',
        ]);

        try {
            $userId = auth()->id(); // null if guest/offline tests
            
            $ledger = $this->inventoryService->adjustStock(
                $validated['product_variant_id'],
                $validated['type'],
                $validated['quantity'],
                $validated['reason'],
                $validated['notes'] ?? null,
                $userId
            );

            return response()->json([
                'success' => true,
                'message' => 'Stock adjusted successfully',
                'data' => $ledger
            ], 201);
        } catch (Exception $e) {
            Log::error('InventoryController@adjust failed: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
                'error_code' => 'BAD_REQUEST'
            ], 400);
        }
    }

    /**
     * Display stock movement ledger history.
     */
    public function ledger(Request $request): JsonResponse
    {
        try {
            $filters = $request->only(['product_variant_id', 'type', 'direction', 'date_from', 'date_to']);
            $perPage = $request->input('per_page', 15);

            $ledger = $this->inventoryService->getPaginatedLedger($filters, $perPage);

            return response()->json([
                'success' => true,
                'message' => 'Inventory ledger records retrieved successfully',
                'data' => $ledger->items(),
                'meta' => [
                    'current_page' => $ledger->currentPage(),
                    'last_page' => $ledger->lastPage(),
                    'per_page' => $ledger->perPage(),
                    'total' => $ledger->total(),
                ]
            ]);
        } catch (Exception $e) {
            Log::error('InventoryController@ledger failed: ' . $e->getMessage());
    /**
     * Post a batch stock adjustment for multiple variants.
     */
    public function batchAdjust(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'items' => 'required|array|min:1',
            'items.*.product_variant_id' => 'required|exists:product_variants,id',
            'items.*.type' => 'required|string|in:adjustment_in,adjustment_out,damage',
            'items.*.quantity' => 'required|integer|min:1',
            'items.*.reason' => 'required|string|max:500',
            'items.*.notes' => 'nullable|string|max:500',
        ]);

        try {
            $userId = auth()->id();
            $results = [];

            foreach ($validated['items'] as $item) {
                $results[] = $this->inventoryService->adjustStock(
                    $item['product_variant_id'],
                    $item['type'],
                    $item['quantity'],
                    $item['reason'],
                    $item['notes'] ?? null,
                    $userId
                );
            }

            return response()->json([
                'success' => true,
                'message' => 'Batch stock adjustments processed successfully',
                'count' => count($results),
                'data' => $results
            ], 201);
        } catch (Exception $e) {
            Log::error('InventoryController@batchAdjust failed: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
                'error_code' => 'BAD_REQUEST'
            ], 400);
        }
    }
}
