<?php

namespace App\Http\Controllers\Api\v1\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\OrderStatusHistory;
use App\Services\InventoryService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Exception;

class OrderController extends Controller
{
    protected $inventoryService;

    public function __construct(InventoryService $inventoryService)
    {
        $this->inventoryService = $inventoryService;
    }

    /**
     * Display listing of orders.
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $query = Order::with('user');

            if ($request->filled('status')) {
                $query->where('status', $request->input('status'));
            }

            if ($request->filled('payment_status')) {
                $query->where('payment_status', $request->input('payment_status'));
            }

            if ($request->filled('search')) {
                $search = $request->input('search');
                $query->where(function ($q) use ($search) {
                    $q->where('order_number', 'like', "%{$search}%")
                      ->orWhere('shipping_first_name', 'like', "%{$search}%")
                      ->orWhere('shipping_last_name', 'like', "%{$search}%")
                      ->orWhereHas('user', function ($uq) use ($search) {
                          $uq->where('email', 'like', "%{$search}%")
                             ->orWhere('first_name', 'like', "%{$search}%")
                             ->orWhere('last_name', 'like', "%{$search}%");
                      });
                });
            }

            if ($request->filled('date_from')) {
                $query->whereDate('created_at', '>=', $request->input('date_from'));
            }

            if ($request->filled('date_to')) {
                $query->whereDate('created_at', '<=', $request->input('date_to'));
            }

            $perPage = $request->input('per_page', 15);
            $orders = $query->orderBy('created_at', 'desc')->paginate($perPage);

            return response()->json([
                'success' => true,
                'message' => 'Orders retrieved successfully',
                'data' => $orders->items(),
                'meta' => [
                    'current_page' => $orders->currentPage(),
                    'last_page' => $orders->lastPage(),
                    'per_page' => $orders->perPage(),
                    'total' => $orders->total(),
                ]
            ]);
        } catch (Exception $e) {
            Log::error('OrderController@index failed: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve orders',
                'error_code' => 'SERVER_ERROR'
            ], 500);
        }
    }

    /**
     * Display detailed order view.
     */
    public function show(int $id): JsonResponse
    {
        try {
            $order = Order::with(['user', 'items.variant.product', 'statusHistory.changedByUser'])->find($id);

            if (!$order) {
                return response()->json([
                    'success' => false,
                    'message' => 'Order not found',
                    'error_code' => 'NOT_FOUND'
                ], 404);
            }

            return response()->json([
                'success' => true,
                'message' => 'Order details retrieved successfully',
                'data' => $order
            ]);
        } catch (Exception $e) {
            Log::error('OrderController@show failed: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve order details',
                'error_code' => 'SERVER_ERROR'
            ], 500);
        }
    }

    /**
     * Update order status.
     */
    public function updateStatus(Request $request, int $id): JsonResponse
    {
        $validated = $request->validate([
            'status' => 'required|string|in:pending,confirmed,processing,shipped,delivered,cancelled,returned',
            'comment' => 'nullable|string|max:500',
        ]);

        try {
            return DB::transaction(function () use ($validated, $id) {
                $order = Order::lockForUpdate()->find($id);

                if (!$order) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Order not found',
                        'error_code' => 'NOT_FOUND'
                    ], 404);
                }

                $fromStatus = $order->status;
                $toStatus = $validated['status'];

                if ($fromStatus === $toStatus) {
                    return response()->json([
                        'success' => true,
                        'message' => 'Order status is already ' . $toStatus,
                        'data' => $order
                    ]);
                }

                // Transition checks
                $validTransitions = [
                    'pending' => ['confirmed', 'cancelled'],
                    'confirmed' => ['processing', 'cancelled'],
                    'processing' => ['shipped', 'cancelled'],
                    'shipped' => ['delivered'],
                    'delivered' => ['returned'],
                    'cancelled' => [],
                    'returned' => [],
                ];

                if (!in_array($toStatus, $validTransitions[$fromStatus] ?? [])) {
                    return response()->json([
                        'success' => false,
                        'message' => "Invalid status transition from '{$fromStatus}' to '{$toStatus}'.",
                        'error_code' => 'INVALID_TRANSITION'
                    ], 400);
                }

                // If transition is processing/shipped, we subtract stock
                if (($toStatus === 'processing' || $toStatus === 'shipped') && in_array($fromStatus, ['pending', 'confirmed'])) {
                    $userId = auth()->id();
                    foreach ($order->items as $item) {
                        $this->inventoryService->postLedgerEntry(
                            $item->product_variant_id,
                            'SALE',
                            'OUT',
                            $item->quantity,
                            null,
                            'Order',
                            $order->id,
                            "Stock adjustment for order #{$order->order_number}",
                            $userId
                        );
                    }
                }

                // If transition is cancelled, we return stock if it was already processed
                if ($toStatus === 'cancelled' && in_array($fromStatus, ['processing', 'shipped', 'delivered'])) {
                    $userId = auth()->id();
                    foreach ($order->items as $item) {
                        $this->inventoryService->postLedgerEntry(
                            $item->product_variant_id,
                            'RETURN',
                            'IN',
                            $item->quantity,
                            null,
                            'Order',
                            $order->id,
                            "Stock return for cancelled order #{$order->order_number}",
                            $userId
                        );
                    }
                    $order->cancelled_at = now();
                }

                if ($toStatus === 'shipped') {
                    $order->shipped_at = now();
                }

                if ($toStatus === 'delivered') {
                    $order->delivered_at = now();
                    $order->payment_status = 'paid';
                }

                $order->status = $toStatus;
                $order->save();

                // Record history
                OrderStatusHistory::create([
                    'order_id' => $order->id,
                    'from_status' => $fromStatus,
                    'to_status' => $toStatus,
                    'comment' => $validated['comment'] ?? "Status updated to {$toStatus}",
                    'changed_by' => auth()->id(),
                ]);

                return response()->json([
                    'success' => true,
                    'message' => "Order status updated to {$toStatus} successfully",
                    'data' => $order
                ]);
            });
        } catch (Exception $e) {
            Log::error('OrderController@updateStatus failed: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to update status: ' . $e->getMessage(),
                'error_code' => 'SERVER_ERROR'
            ], 500);
        }
    }

    /**
     * Update order shipping tracking details.
     */
    public function updateShipping(Request $request, int $id): JsonResponse
    {
        $validated = $request->validate([
            'courier_name' => 'required|string|max:100',
            'tracking_number' => 'required|string|max:100',
            'estimated_delivery_at' => 'nullable|date',
        ]);

        try {
            $order = Order::find($id);

            if (!$order) {
                return response()->json([
                    'success' => false,
                    'message' => 'Order not found',
                    'error_code' => 'NOT_FOUND'
                ], 404);
            }

            $order->courier_name = $validated['courier_name'];
            $order->tracking_number = $validated['tracking_number'];
            if (isset($validated['estimated_delivery_at'])) {
                $order->estimated_delivery_at = $validated['estimated_delivery_at'];
            }
            $order->save();

            return response()->json([
                'success' => true,
                'message' => 'Shipping information updated successfully',
                'data' => $order
            ]);
        } catch (Exception $e) {
            Log::error('OrderController@updateShipping failed: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to update shipping details',
                'error_code' => 'SERVER_ERROR'
            ], 500);
        }
    }

    /**
     * Append administrative note.
     */
    public function addAdminNote(Request $request, int $id): JsonResponse
    {
        $validated = $request->validate([
            'note' => 'required|string|max:1000',
        ]);

        try {
            $order = Order::find($id);

            if (!$order) {
                return response()->json([
                    'success' => false,
                    'message' => 'Order not found',
                    'error_code' => 'NOT_FOUND'
                ], 404);
            }

            $timestamp = now()->format('Y-m-d H:i:s');
            $userLabel = auth()->user()?->name ?? 'Admin';
            $formattedNote = "[{$timestamp} - {$userLabel}]: {$validated['note']}";

            if (empty($order->admin_notes)) {
                $order->admin_notes = $formattedNote;
            } else {
                $order->admin_notes = $order->admin_notes . "\n" . $formattedNote;
            }

            $order->save();

            return response()->json([
                'success' => true,
                'message' => 'Admin note added successfully',
                'data' => $order
            ]);
        } catch (Exception $e) {
            Log::error('OrderController@addAdminNote failed: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to add administrative note',
                'error_code' => 'SERVER_ERROR'
            ], 500);
        }
    }
}
