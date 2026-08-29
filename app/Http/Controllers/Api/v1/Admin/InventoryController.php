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
     * Display real-time Stock Overview with global KPIs and product stock cards.
     */
    public function overview(Request $request): JsonResponse
    {
        try {
            $filters = $request->only(['search', 'status', 'category_id', 'sort_by', 'page']);
            $perPage = min(max((int) $request->input('per_page', 24), 1), 100);

            $stats = $this->inventoryService->getStockOverviewStats($filters);
            $products = $this->inventoryService->getStockOverview($filters, $perPage);

            return response()->json([
                'success' => true,
                'message' => 'Stock overview data retrieved successfully',
                'stats' => $stats,
                'data' => $products->items(),
                'meta' => [
                    'current_page' => $products->currentPage(),
                    'last_page' => $products->lastPage(),
                    'per_page' => $products->perPage(),
                    'total' => $products->total(),
                ]
            ]);
        } catch (\Throwable $e) {
            Log::error('InventoryController@overview failed: ' . $e->getMessage() . "\n" . $e->getTraceAsString());
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve stock overview',
                'error_detail' => config('app.debug') ? $e->getMessage() : null,
                'error_code' => 'SERVER_ERROR'
            ], 500);
        }
    }

    /**
     * Quick color & size variant stock adjustment for a product.
     */
    public function quickAdjust(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'product_id' => 'required|exists:products,id',
            'mode' => 'required|string|in:set,add,subtract',
            'reason' => 'required|string|max:255',
            'notes' => 'nullable|string|max:500',
            'items' => 'required|array|min:1',
            'items.*.variant_id' => 'required|integer|exists:product_variants,id',
            'items.*.quantity' => 'required|integer|min:0',
        ]);

        try {
            $userId = auth()->id();
            $result = $this->inventoryService->bulkUpdateMatrixStock(
                $validated['product_id'],
                $validated['mode'],
                $validated['reason'],
                $validated['notes'] ?? null,
                $validated['items'],
                $userId
            );

            return response()->json([
                'success' => true,
                'message' => "Stock updated successfully for {$result['updated_count']} variants.",
                'data' => $result,
            ]);
        } catch (\Throwable $e) {
            Log::error('InventoryController@quickAdjust failed: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => $e->getMessage() ?: 'Failed to adjust product stock',
                'error_code' => 'BAD_REQUEST'
            ], 400);
        }
    }

    /**
     * Export full stock overview as CSV.
     */
    public function exportOverviewCsv(Request $request)
    {
        try {
            $filters = $request->only(['search', 'status', 'category_id', 'sort_by']);
            $products = $this->inventoryService->getStockOverview($filters, 1000);
            $filename = "stock_overview_" . date('Y_m_d_His') . ".csv";

            $headers = [
                'Content-Type' => 'text/csv',
                'Content-Disposition' => "attachment; filename=\"{$filename}\"",
                'Pragma' => 'no-cache',
                'Cache-Control' => 'must-revalidate, post-check=0, pre-check=0',
                'Expires' => '0',
            ];

            $callback = function () use ($products) {
                $file = fopen('php://output', 'w');
                fputcsv($file, ['Product ID', 'Product Name', 'SKU', 'Category', 'Stock Qty', 'Order Qty', 'Available Qty', 'Status', 'Last Updated']);

                foreach ($products as $p) {
                    fputcsv($file, [
                        $p['id'],
                        $p['name'],
                        $p['sku'],
                        $p['category_name'],
                        $p['stock_qty'],
                        $p['order_qty'],
                        $p['avail_qty'],
                        $p['status_label'],
                        $p['updated_at_formatted'],
                    ]);
                }
                fclose($file);
            };

            return response()->stream($callback, 200, $headers);
        } catch (\Throwable $e) {
            Log::error('InventoryController@exportOverviewCsv failed: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to export stock overview.',
            ], 500);
        }
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
        } catch (\Throwable $e) {
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
        } catch (\Throwable $e) {
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
        } catch (\Throwable $e) {
            Log::error('InventoryController@ledger failed: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve stock ledger history',
                'error_code' => 'SERVER_ERROR'
            ], 500);
        }
    }

    /**
     * Get Color x Size stock matrix for a product.
     */
    public function productMatrix(int $id): JsonResponse
    {
        try {
            $data = $this->inventoryService->getProductStockMatrix($id);
            return response()->json([
                'success' => true,
                'data' => $data,
            ]);
        } catch (\Throwable $e) {
            Log::error("InventoryController@productMatrix failed for Product #{$id}: " . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => $e->getMessage() ?: 'Failed to load product stock matrix',
            ], 404);
        }
    }

    /**
     * Bulk update stock across variant matrix.
     */
    public function bulkMatrixUpdate(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'product_id' => 'required|exists:products,id',
            'mode' => 'required|string|in:set,add,subtract',
            'reason' => 'required|string|max:255',
            'notes' => 'nullable|string|max:500',
            'items' => 'required|array|min:1',
            'items.*.variant_id' => 'required|integer',
            'items.*.quantity' => 'required|integer|min:0',
        ]);

        try {
            $userId = auth()->id();
            $result = $this->inventoryService->bulkUpdateMatrixStock(
                $validated['product_id'],
                $validated['mode'],
                $validated['reason'],
                $validated['notes'] ?? null,
                $validated['items'],
                $userId
            );

            return response()->json([
                'success' => true,
                'message' => "Stock updated successfully for {$result['updated_count']} variants.",
                'data' => $result,
            ]);
        } catch (\Throwable $e) {
            Log::error('InventoryController@bulkMatrixUpdate failed: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => $e->getMessage() ?: 'Failed to update stock matrix',
            ], 400);
        }
    }

    /**
     * Export a CSV stock entry template for a product.
     */
    public function exportTemplate(int $id)
    {
        try {
            $matrixData = $this->inventoryService->getProductStockMatrix($id);
            $productName = preg_replace('/[^A-Za-z0-9_-]/', '_', $matrixData['product']['name']);
            $filename = "stock_template_{$productName}_{$id}.csv";

            $headers = [
                'Content-Type' => 'text/csv',
                'Content-Disposition' => "attachment; filename=\"{$filename}\"",
                'Pragma' => 'no-cache',
                'Cache-Control' => 'must-revalidate, post-check=0, pre-check=0',
                'Expires' => '0',
            ];

            $callback = function () use ($matrixData) {
                $file = fopen('php://output', 'w');
                // CSV header
                fputcsv($file, ['variant_id', 'sku', 'color', 'size', 'current_stock', 'new_quantity']);

                foreach ($matrixData['variants'] as $v) {
                    fputcsv($file, [
                        $v->id,
                        $v->sku,
                        $v->color,
                        $v->size,
                        $v->stock_quantity,
                        $v->stock_quantity, // default to current
                    ]);
                }
                fclose($file);
            };

            return response()->stream($callback, 200, $headers);
        } catch (\Throwable $e) {
            Log::error("InventoryController@exportTemplate failed: " . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to export stock template.',
            ], 404);
        }
    }

    /**
     * Import CSV stock file and apply batch update.
     */
    public function importCsv(Request $request): JsonResponse
    {
        $request->validate([
            'product_id' => 'required|exists:products,id',
            'file' => 'required|file|mimes:csv,txt|max:5120',
            'mode' => 'required|string|in:set,add,subtract',
            'reason' => 'required|string|max:255',
            'notes' => 'nullable|string|max:500',
        ]);

        try {
            $file = $request->file('file');
            $path = $file->getRealPath();
            $handle = fopen($path, 'r');
            
            if (!$handle) {
                return response()->json(['success' => false, 'message' => 'Could not read CSV file.'], 422);
            }

            $header = fgetcsv($handle);
            $header = array_map('strtolower', array_map('trim', $header ?: []));

            $variantIdIdx = array_search('variant_id', $header);
            $skuIdx = array_search('sku', $header);
            $newQtyIdx = array_search('new_quantity', $header);
            if ($newQtyIdx === false) {
                $newQtyIdx = array_search('quantity', $header);
            }

            if ($newQtyIdx === false) {
                fclose($handle);
                return response()->json([
                    'success' => false,
                    'message' => "CSV must contain a 'new_quantity' or 'quantity' column.",
                ], 422);
            }

            $items = [];
            $productId = (int) $request->input('product_id');

            while (($row = fgetcsv($handle)) !== false) {
                if (empty($row) || count($row) < 2) continue;

                $variantId = $variantIdIdx !== false && isset($row[$variantIdIdx]) ? (int) trim($row[$variantIdIdx]) : 0;
                $sku = $skuIdx !== false && isset($row[$skuIdx]) ? trim($row[$skuIdx]) : '';
                $qty = isset($row[$newQtyIdx]) ? (int) trim($row[$newQtyIdx]) : 0;

                if (!$variantId && $sku) {
                    $variant = \App\Models\ProductVariant::where('product_id', $productId)->where('sku', $sku)->first();
                    if ($variant) {
                        $variantId = $variant->id;
                    }
                }

                if ($variantId) {
                    $items[] = [
                        'variant_id' => $variantId,
                        'quantity' => $qty,
                    ];
                }
            }
            fclose($handle);

            if (empty($items)) {
                return response()->json([
                    'success' => false,
                    'message' => 'No valid variant rows were found in the uploaded CSV.',
                ], 422);
            }

            $userId = auth()->id();
            $result = $this->inventoryService->bulkUpdateMatrixStock(
                $productId,
                $request->input('mode'),
                $request->input('reason'),
                $request->input('notes'),
                $items,
                $userId
            );

            return response()->json([
                'success' => true,
                'message' => "Successfully imported and updated {$result['updated_count']} variants from CSV.",
                'data' => $result,
            ]);
        } catch (\Throwable $e) {
            Log::error('InventoryController@importCsv failed: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => $e->getMessage() ?: 'Failed to process CSV file.',
            ], 422);
        }
    }
}
