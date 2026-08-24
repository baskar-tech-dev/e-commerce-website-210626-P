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
            $order = Order::with(['user', 'items.variant.product', 'statusHistory.changedByUser', 'payments'])->find($id);

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
    /**
     * Get all 9 official order statuses metadata.
     */
    public function statuses(): JsonResponse
    {
        return response()->json([
            'success' => true,
            'data' => array_values(Order::STATUSES),
        ]);
    }

    /**
     * Update order status.
     */
    public function updateStatus(Request $request, int $id): JsonResponse
    {
        $validated = $request->validate([
            'status' => 'required|string|in:order_placed,order_confirmed,processing,ready_to_ship,shipped,delivered,cancelled,returned,refunded,pending,confirmed',
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

                $fromStatus = $order->normalizeStatus($order->status);
                $toStatus = $order->normalizeStatus($validated['status']);

                if ($fromStatus === $toStatus) {
                    return response()->json([
                        'success' => true,
                        'message' => 'Order status is already ' . ($order->status_label ?: $toStatus),
                        'data' => $order
                    ]);
                }

                // 9 Status Transition Rules
                $validTransitions = [
                    'order_placed' => ['order_confirmed', 'processing', 'cancelled'],
                    'order_confirmed' => ['processing', 'ready_to_ship', 'cancelled'],
                    'processing' => ['ready_to_ship', 'shipped', 'cancelled'],
                    'ready_to_ship' => ['shipped', 'cancelled'],
                    'shipped' => ['delivered', 'returned', 'cancelled'],
                    'delivered' => ['returned', 'refunded'],
                    'returned' => ['refunded'],
                    'cancelled' => ['refunded'],
                    'refunded' => [],
                ];

                $allowed = $validTransitions[$fromStatus] ?? [];
                
                // Allow admin override if requested or in transition table
                if (!in_array($toStatus, $allowed) && !$request->boolean('force_override')) {
                    $allowedLabels = array_map(function ($s) {
                        return Order::STATUSES[$s]['label'] ?? $s;
                    }, $allowed);
                    $allowedText = !empty($allowedLabels) ? implode(', ', $allowedLabels) : 'None (Terminal state)';

                    return response()->json([
                        'success' => false,
                        'message' => "Cannot transition from '{$order->status_label}' to '" . (Order::STATUSES[$toStatus]['label'] ?? $toStatus) . "'. Allowed next steps: {$allowedText}.",
                        'allowed_transitions' => $allowed,
                        'error_code' => 'INVALID_TRANSITION'
                    ], 400);
                }

                // If transition is processing/ready_to_ship/shipped, we subtract stock if from placed/confirmed
                if (in_array($toStatus, ['processing', 'ready_to_ship', 'shipped']) && in_array($fromStatus, ['order_placed', 'order_confirmed', 'pending', 'confirmed'])) {
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

                // If transition is cancelled or returned, we return stock if it was already in processing/shipped/delivered
                if (in_array($toStatus, ['cancelled', 'returned']) && in_array($fromStatus, ['processing', 'ready_to_ship', 'shipped', 'delivered'])) {
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
                            "Stock return for {$toStatus} order #{$order->order_number}",
                            $userId
                        );
                    }
                    if ($toStatus === 'cancelled') {
                        $order->cancelled_at = now();
                    }
                }

                if ($toStatus === 'shipped') {
                    $order->shipped_at = now();
                }

                if ($toStatus === 'delivered') {
                    $order->delivered_at = now();
                    if ($order->payment_method === 'cod') {
                        $order->payment_status = 'paid';
                    }
                }

                if ($toStatus === 'refunded') {
                    $order->payment_status = 'refunded';
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
            'courier_id' => 'nullable|exists:couriers,id',
            'courier_name' => 'required|string|max:100',
            'tracking_number' => 'required|string|max:100',
            'courier_tracking_url' => 'nullable|string|max:500',
            'courier_contact_number' => 'nullable|string|max:50',
            'courier_person_name' => 'nullable|string|max:100',
            'estimated_delivery_at' => 'nullable|date',
            'shipped_at' => 'nullable|date',
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

            $order->courier_id = $validated['courier_id'] ?? null;
            $order->courier_name = $validated['courier_name'];
            $order->tracking_number = $validated['tracking_number'];
            $order->courier_contact_number = $validated['courier_contact_number'] ?? null;
            $order->courier_person_name = $validated['courier_person_name'] ?? null;

            // Resolve tracking URL if template exists and direct url not manually given
            if (!empty($validated['courier_tracking_url'])) {
                $order->courier_tracking_url = $validated['courier_tracking_url'];
            } elseif (!empty($order->courier_id)) {
                $courier = \App\Models\Courier::find($order->courier_id);
                if ($courier) {
                    $order->courier_tracking_url = $courier->generateTrackingUrl($validated['tracking_number']);
                    if (empty($order->courier_contact_number)) {
                        $order->courier_contact_number = $courier->contact_number;
                    }
                    if (empty($order->courier_person_name)) {
                        $order->courier_person_name = $courier->contact_person;
                    }
                }
            }

            if (isset($validated['estimated_delivery_at'])) {
                $order->estimated_delivery_at = $validated['estimated_delivery_at'];
            }
            if (isset($validated['shipped_at'])) {
                $order->shipped_at = $validated['shipped_at'];
            }

            $order->save();
            $order->load(['courier']);

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

    /**
     * Delete order (Soft delete / remove from list).
     */
    public function destroy(int $id): JsonResponse
    {
        try {
            $order = Order::find($id);

            if (!$order) {
                return response()->json([
                    'success' => false,
                    'message' => 'Order not found',
                    'error_code' => 'NOT_FOUND'
                ], 404);
            }

            $orderNo = $order->order_number;
            $order->delete();

            Log::info("Order #{$orderNo} (ID: {$id}) deleted by Admin (User ID: " . (auth()->id() ?? 'system') . ")");

            return response()->json([
                'success' => true,
                'message' => "Order #{$orderNo} has been deleted successfully",
            ]);
        } catch (Exception $e) {
            Log::error('OrderController@destroy failed: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete order: ' . $e->getMessage(),
                'error_code' => 'SERVER_ERROR'
            ], 500);
        }
    }
}
