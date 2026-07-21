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

    /**
     * Generate HTML Printable Tax Invoice for an order.
     */
    public function invoice(int $id)
    {
        $order = Order::with(['items.variant.product', 'user'])->findOrFail($id);

        $html = "
        <!DOCTYPE html>
        <html>
        <head>
            <title>TAX INVOICE - #{$order->order_number}</title>
            <style>
                body { font-family: 'Helvetica Neue', Arial, sans-serif; margin: 0; padding: 30px; color: #2d2d2d; font-size: 14px; }
                .header { display: flex; justify-content: space-between; border-bottom: 2px solid #4a0e2e; padding-bottom: 15px; margin-bottom: 20px; }
                .brand-title { font-size: 24px; font-weight: bold; color: #4a0e2e; }
                .invoice-title { font-size: 20px; text-align: right; color: #d4af37; font-weight: bold; }
                .grid-2 { display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-bottom: 20px; }
                .card { background: #fafafa; border: 1px solid #e2e8f0; border-radius: 8px; padding: 15px; }
                table { width: 100%; border-collapse: collapse; margin-top: 15px; }
                th { background: #4a0e2e; color: #ffffff; text-align: left; padding: 10px; font-size: 12px; }
                td { padding: 10px; border-bottom: 1px solid #e2e8f0; }
                .total-row { font-weight: bold; font-size: 16px; color: #4a0e2e; }
                @media print { body { padding: 0; } }
            </style>
        </head>
        <body>
            <div class='header'>
                <div>
                    <div class='brand-title'>MAYA SREE FASHION</div>
                    <div style='color: #64748b; font-size: 12px; margin-top: 4px;'>South Indian Ethnic Wear & Designer Ensembles</div>
                    <div style='font-size: 11px; color: #64748b;'>GSTIN: 33AAAAA0000A1Z5 | Chennai, Tamil Nadu</div>
                </div>
                <div class='invoice-title'>
                    TAX INVOICE<br>
                    <span style='font-size: 13px; color: #0f172a;'>#{$order->order_number}</span>
                </div>
            </div>

            <div class='grid-2'>
                <div class='card'>
                    <strong>Billed & Shipped To:</strong><br>
                    {$order->shipping_first_name} {$order->shipping_last_name}<br>
                    Phone: {$order->shipping_phone}<br>
                    {$order->shipping_address_line_1}<br>
                    " . ($order->shipping_address_line_2 ? $order->shipping_address_line_2 . "<br>" : "") . "
                    {$order->shipping_city}, {$order->shipping_state} - {$order->shipping_postal_code}
                </div>
                <div class='card'>
                    <strong>Order Details:</strong><br>
                    Date: " . $order->created_at->format('d M Y, h:i A') . "<br>
                    Payment Method: " . strtoupper($order->payment_method) . "<br>
                    Payment Status: " . strtoupper($order->payment_status) . "<br>
                    Courier: " . ($order->courier_name ?? 'Standard Dispatch') . "<br>
                    Tracking ID: " . ($order->tracking_number ?? 'Pending') . "
                </div>
            </div>

            <table>
                <thead>
                    <tr>
                        <th>ITEM DESCRIPTION</th>
                        <th>SKU / SIZE</th>
                        <th>QTY</th>
                        <th>UNIT PRICE</th>
                        <th>AMOUNT</th>
                    </tr>
                </thead>
                <tbody>";

        foreach ($order->items as $item) {
            $name = htmlspecialchars($item->name);
            $sku = htmlspecialchars($item->variant->sku ?? 'N/A');
            $size = htmlspecialchars($item->variant->size ?? 'OS');
            $sub = number_format($item->unit_price * $item->quantity, 2);
            $price = number_format($item->unit_price, 2);

            $html .= "
                    <tr>
                        <td>{$name}</td>
                        <td>{$sku} ({$size})</td>
                        <td>{$item->quantity}</td>
                        <td>₹{$price}</td>
                        <td>₹{$sub}</td>
                    </tr>";
        }

        $grandTotal = number_format($order->grand_total, 2);
        $discount = number_format($order->discount_amount, 2);
        $shipping = number_format($order->shipping_cost, 2);

        $html .= "
                </tbody>
            </table>

            <div style='margin-top: 20px; text-align: right; font-size: 14px;'>
                <div>Subtotal: ₹" . number_format($order->subtotal, 2) . "</div>
                " . ($order->discount_amount > 0 ? "<div style='color: #16a34a;'>Voucher Discount: -₹{$discount}</div>" : "") . "
                <div>Shipping: ₹{$shipping}</div>
                <div class='total-row' style='margin-top: 8px;'>Grand Total: ₹{$grandTotal}</div>
            </div>

            <div style='margin-top: 40px; border-top: 1px solid #e2e8f0; padding-top: 15px; text-align: center; color: #64748b; font-size: 12px;'>
                Thank you for your purchase with Maya Sree South Indian Fashion! For support, email support@mayasree.com
            </div>
            <script>window.print();</script>
        </body>
        </html>";

        return response($html)->header('Content-Type', 'text/html');
    }

    /**
     * Generate HTML Printable Packing Slip for warehouse dispatch.
     */
    public function packingSlip(int $id)
    {
        $order = Order::with(['items.variant.product'])->findOrFail($id);

        $html = "
        <!DOCTYPE html>
        <html>
        <head>
            <title>PACKING SLIP - #{$order->order_number}</title>
            <style>
                body { font-family: sans-serif; padding: 25px; color: #1e293b; }
                .title { font-size: 22px; font-weight: bold; border-bottom: 2px solid #0f172a; padding-bottom: 10px; margin-bottom: 20px; }
                .box { border: 1px solid #cbd5e1; border-radius: 8px; padding: 15px; margin-bottom: 20px; background: #f8fafc; }
                table { width: 100%; border-collapse: collapse; margin-top: 15px; }
                th, td { padding: 10px; border: 1px solid #cbd5e1; text-align: left; }
                th { background: #f1f5f9; }
                @media print { body { padding: 0; } }
            </style>
        </head>
        <body>
            <div class='title'>MAYA SREE FASHION - WAREHOUSE PACKING SLIP</div>
            
            <div class='box'>
                <strong>Order Reference:</strong> #{$order->order_number}<br>
                <strong>Order Date:</strong> " . $order->created_at->format('d M Y, h:i A') . "<br>
                <strong>Shipping Address:</strong><br>
                {$order->shipping_first_name} {$order->shipping_last_name} ({$order->shipping_phone})<br>
                {$order->shipping_address_line_1}, {$order->shipping_address_line_2}<br>
                {$order->shipping_city}, {$order->shipping_state} - {$order->shipping_postal_code}
            </div>

            <table>
                <thead>
                    <tr>
                        <th>VERIFIED</th>
                        <th>ITEM SKU</th>
                        <th>PRODUCT NAME</th>
                        <th>SIZE / COLOR</th>
                        <th>QTY PICKED</th>
                    </tr>
                </thead>
                <tbody>";

        foreach ($order->items as $item) {
            $sku = htmlspecialchars($item->variant->sku ?? 'SKU-NONE');
            $name = htmlspecialchars($item->name);
            $size = htmlspecialchars($item->variant->size ?? 'OS');
            $color = htmlspecialchars($item->variant->color ?? '-');

            $html .= "
                    <tr>
                        <td style='text-align: center;'>[ &nbsp; ]</td>
                        <td><strong>{$sku}</strong></td>
                        <td>{$name}</td>
                        <td>{$size} / {$color}</td>
                        <td style='font-size: 16px; font-weight: bold;'>{$item->quantity}</td>
                    </tr>";
        }

        $html .= "
                </tbody>
            </table>

            <div style='margin-top: 30px; font-size: 12px; color: #64748b;'>
                Packed By: ____________________ &nbsp;&nbsp;&nbsp;&nbsp; Inspected By: ____________________
            </div>
            <script>window.print();</script>
        </body>
        </html>";

        return response($html)->header('Content-Type', 'text/html');
    }

    /**
     * Generate HTML Printable AWB Shipping Label.
     */
    public function awbLabel(int $id, \App\Services\ShippingService $shippingService)
    {
        $order = Order::findOrFail($id);
        $html = $shippingService->generateAwbLabel($order);
        return response($html)->header('Content-Type', 'text/html');
    }

    /**
     * Generate HTML Printable Pickup Manifest.
     */
    public function manifest(Request $request, \App\Services\ShippingService $shippingService)
    {
        $orderIds = $request->input('order_ids', []);
        $query = Order::query();
        if (!empty($orderIds)) {
            $query->whereIn('id', $orderIds);
        } else {
            $query->whereIn('status', ['confirmed', 'processing', 'shipped'])->take(20);
        }
        $orders = $query->get()->all();

        $html = $shippingService->generateManifest($orders);
        return response($html)->header('Content-Type', 'text/html');
    }
}
