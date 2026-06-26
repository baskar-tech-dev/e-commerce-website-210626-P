<?php

namespace App\Http\Controllers\Api\v1\Admin;

use App\Http\Controllers\Controller;
use App\Services\PurchaseOrderService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Exception;

class PurchaseOrderController extends Controller
{
    protected $purchaseOrderService;

    public function __construct(PurchaseOrderService $purchaseOrderService)
    {
        $this->purchaseOrderService = $purchaseOrderService;
    }

    /**
     * Display listing of purchase orders.
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $filters = $request->only(['status', 'supplier_name', 'date_from', 'date_to']);
            $perPage = $request->input('per_page', 15);

            $pos = $this->purchaseOrderService->getPaginatedPurchaseOrders($filters, $perPage);

            return response()->json([
                'success' => true,
                'message' => 'Purchase orders retrieved successfully',
                'data' => $pos->items(),
                'meta' => [
                    'current_page' => $pos->currentPage(),
                    'last_page' => $pos->lastPage(),
                    'per_page' => $pos->perPage(),
                    'total' => $pos->total(),
                ]
            ]);
        } catch (Exception $e) {
            Log::error('PurchaseOrderController@index failed: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve purchase orders',
                'error_code' => 'SERVER_ERROR'
            ], 500);
        }
    }

    /**
     * Store a newly created purchase order.
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'supplier_name' => 'required|string|max:200',
            'supplier_contact' => 'nullable|string|max:200',
            'notes' => 'nullable|string',
            'items' => 'required|array|min:1',
            'items.*.product_variant_id' => 'required|exists:product_variants,id',
            'items.*.quantity_ordered' => 'required|integer|min:1',
            'items.*.unit_cost' => 'required|numeric|min:0',
        ]);

        try {
            $userId = auth()->id();
            $po = $this->purchaseOrderService->createPurchaseOrder($validated, $userId);

            return response()->json([
                'success' => true,
                'message' => 'Purchase order draft created successfully',
                'data' => $po
            ], 201);
        } catch (Exception $e) {
            Log::error('PurchaseOrderController@store failed: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to create purchase order: ' . $e->getMessage(),
                'error_code' => 'SERVER_ERROR'
            ], 500);
        }
    }

    /**
     * Display details of a purchase order.
     */
    public function show(int $id): JsonResponse
    {
        try {
            $po = $this->purchaseOrderService->getPurchaseOrderById($id);

            if (!$po) {
                return response()->json([
                    'success' => false,
                    'message' => 'Purchase order not found',
                    'error_code' => 'NOT_FOUND'
                ], 404);
            }

            return response()->json([
                'success' => true,
                'message' => 'Purchase order details retrieved successfully',
                'data' => $po
            ]);
        } catch (Exception $e) {
            Log::error('PurchaseOrderController@show failed: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve purchase order details',
                'error_code' => 'SERVER_ERROR'
            ], 500);
        }
    }

    /**
     * Update the specified purchase order (draft status only).
     */
    public function update(Request $request, int $id): JsonResponse
    {
        $validated = $request->validate([
            'supplier_name' => 'sometimes|required|string|max:200',
            'supplier_contact' => 'nullable|string|max:200',
            'notes' => 'nullable|string',
            'status' => 'nullable|string|in:draft,ordered,cancelled',
            'items' => 'sometimes|required|array|min:1',
            'items.*.product_variant_id' => 'required|exists:product_variants,id',
            'items.*.quantity_ordered' => 'required|integer|min:1',
            'items.*.unit_cost' => 'required|numeric|min:0',
        ]);

        try {
            $po = $this->purchaseOrderService->updatePurchaseOrder($id, $validated);

            if (!$po) {
                return response()->json([
                    'success' => false,
                    'message' => 'Purchase order not found',
                    'error_code' => 'NOT_FOUND'
                ], 404);
            }

            return response()->json([
                'success' => true,
                'message' => 'Purchase order updated successfully',
                'data' => $po
            ]);
        } catch (Exception $e) {
            Log::error('PurchaseOrderController@update failed: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
                'error_code' => 'BAD_REQUEST'
            ], 400);
        }
    }

    /**
     * Remove the specified purchase order (draft status only).
     */
    public function destroy(int $id): JsonResponse
    {
        try {
            $deleted = $this->purchaseOrderService->deletePurchaseOrder($id);

            if (!$deleted) {
                return response()->json([
                    'success' => false,
                    'message' => 'Purchase order not found or is already locked',
                    'error_code' => 'NOT_FOUND'
                ], 404);
            }

            return response()->json([
                'success' => true,
                'message' => 'Purchase order draft deleted successfully'
            ]);
        } catch (Exception $e) {
            Log::error('PurchaseOrderController@destroy failed: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
                'error_code' => 'BAD_REQUEST'
            ], 400);
        }
    }

    /**
     * Receive quantities and stock batch arrivals for ordered purchase orders.
     */
    public function receive(Request $request, int $id): JsonResponse
    {
        $validated = $request->validate([
            'items' => 'required|array|min:1',
            'items.*.item_id' => 'required|exists:purchase_order_items,id',
            'items.*.quantity_received' => 'required|integer|min:0',
        ]);

        try {
            $userId = auth()->id();
            $po = $this->purchaseOrderService->receiveStock($id, $validated, $userId);

            return response()->json([
                'success' => true,
                'message' => 'Stock received and inventory levels updated successfully',
                'data' => $po
            ]);
        } catch (Exception $e) {
            Log::error('PurchaseOrderController@receive failed: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
                'error_code' => 'BAD_REQUEST'
            ], 400);
        }
    }
}
