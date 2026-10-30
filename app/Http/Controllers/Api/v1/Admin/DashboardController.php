<?php

namespace App\Http\Controllers\Api\v1\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Product;
use App\Models\CustomerProfile;
use App\Models\ProductVariant;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class DashboardController extends Controller
{
    /**
     * Get dashboard stats filtered for Paid orders, plus a dedicated section for Pending, Failed, and Refunded payments.
     */
    public function stats(Request $request): JsonResponse
    {
        try {
            $today = now()->toDateString();
            $currentMonth = now()->month;
            $currentYear = now()->year;

            // =========================================================================
            // 1. Core KPIs: Show records ONLY for Payment PAID
            // =========================================================================
            $paidQuery = Order::where('payment_status', 'paid');

            $totalOrdersCount = (clone $paidQuery)->count();
            $totalRevenue = (float) (clone $paidQuery)->where('status', '!=', 'cancelled')->sum('grand_total');

            $todayOrdersCount = (clone $paidQuery)->whereDate('created_at', $today)->count();
            $todayRevenue = (float) (clone $paidQuery)
                ->whereDate('created_at', $today)
                ->where('status', '!=', 'cancelled')
                ->sum('grand_total');

            $monthOrdersCount = (clone $paidQuery)
                ->whereMonth('created_at', $currentMonth)
                ->whereYear('created_at', $currentYear)
                ->count();
            $monthRevenue = (float) (clone $paidQuery)
                ->whereMonth('created_at', $currentMonth)
                ->whereYear('created_at', $currentYear)
                ->where('status', '!=', 'cancelled')
                ->sum('grand_total');

            $totalProductsCount = Product::where('is_active', true)->count();
            $totalCustomersCount = CustomerProfile::count() ?: \App\Models\User::where('role_id', '!=', 1)->count();

            // =========================================================================
            // 2. 9-Stage Order Lifecycle Pipeline (PAID Orders Only, with Date Filtering)
            // =========================================================================
            $pipelineQuery = clone $paidQuery;
            $pipelineDateFrom = $request->input('pipeline_date_from');
            $pipelineDateTo = $request->input('pipeline_date_to');

            if (!empty($pipelineDateFrom)) {
                $pipelineQuery->whereDate('created_at', '>=', $pipelineDateFrom);
            }

            if (!empty($pipelineDateTo)) {
                $pipelineQuery->whereDate('created_at', '<=', $pipelineDateTo);
            }

            $pipeline = [
                'order_placed' => [
                    'label' => 'Order Placed',
                    'icon' => '📝',
                    'count' => (clone $pipelineQuery)->whereIn('status', ['order_placed', 'pending'])->count(),
                    'amount' => round((float) (clone $pipelineQuery)->whereIn('status', ['order_placed', 'pending'])->sum('grand_total'), 2),
                    'code' => 'order_placed',
                ],
                'order_confirmed' => [
                    'label' => 'Order Confirmed',
                    'icon' => '✓',
                    'count' => (clone $pipelineQuery)->whereIn('status', ['order_confirmed', 'confirmed'])->count(),
                    'amount' => round((float) (clone $pipelineQuery)->whereIn('status', ['order_confirmed', 'confirmed'])->sum('grand_total'), 2),
                    'code' => 'order_confirmed',
                ],
                'processing' => [
                    'label' => 'Processing',
                    'icon' => '⚙️',
                    'count' => (clone $pipelineQuery)->where('status', 'processing')->count(),
                    'amount' => round((float) (clone $pipelineQuery)->where('status', 'processing')->sum('grand_total'), 2),
                    'code' => 'processing',
                ],
                'ready_to_ship' => [
                    'label' => 'Ready to Ship',
                    'icon' => '📦',
                    'count' => (clone $pipelineQuery)->where('status', 'ready_to_ship')->count(),
                    'amount' => round((float) (clone $pipelineQuery)->where('status', 'ready_to_ship')->sum('grand_total'), 2),
                    'code' => 'ready_to_ship',
                ],
                'shipped' => [
                    'label' => 'Shipped',
                    'icon' => '🚚',
                    'count' => (clone $pipelineQuery)->where('status', 'shipped')->count(),
                    'amount' => round((float) (clone $pipelineQuery)->where('status', 'shipped')->sum('grand_total'), 2),
                    'code' => 'shipped',
                ],
                'delivered' => [
                    'label' => 'Delivered',
                    'icon' => '🎉',
                    'count' => (clone $pipelineQuery)->where('status', 'delivered')->count(),
                    'amount' => round((float) (clone $pipelineQuery)->where('status', 'delivered')->sum('grand_total'), 2),
                    'code' => 'delivered',
                ],
                'cancelled' => [
                    'label' => 'Cancelled',
                    'icon' => '✕',
                    'count' => (clone $pipelineQuery)->where('status', 'cancelled')->count(),
                    'amount' => round((float) (clone $pipelineQuery)->where('status', 'cancelled')->sum('grand_total'), 2),
                    'code' => 'cancelled',
                ],
            ];

            // =========================================================================
            // 3. Low Stock Alerts
            // =========================================================================
            $lowStockQuery = ProductVariant::with('product')
                ->where('stock_quantity', '<=', DB::raw('low_stock_threshold'));
            
            $totalLowStockCount = (clone $lowStockQuery)->count();

            $lowStockVariants = $lowStockQuery->orderBy('stock_quantity', 'asc')
                ->take(6)
                ->get()
                ->map(function ($variant) {
                    return [
                        'id' => $variant->id,
                        'product_id' => $variant->product_id,
                        'product_name' => $variant->product->name ?? 'Product SKU',
                        'sku' => $variant->sku,
                        'size' => $variant->size,
                        'color' => $variant->color,
                        'stock_quantity' => $variant->stock_quantity,
                        'threshold' => $variant->low_stock_threshold,
                    ];
                });

            // Format helper for orders
            $formatOrderRow = function ($order) {
                $fullName = trim(($order->shipping_first_name ?? '') . ' ' . ($order->shipping_last_name ?? ''));
                $timeAgo = 'Recently';
                if ($order->created_at) {
                    $timeAgo = $order->created_at->isFuture() ? 'Just now' : $order->created_at->diffForHumans();
                }
                return [
                    'id' => $order->id,
                    'order_number' => $order->order_number,
                    'customer_name' => $fullName ?: ($order->user->name ?? 'Customer'),
                    'customer_email' => $order->user->email ?? '—',
                    'items_count' => $order->total_items ?: 1,
                    'grand_total' => (float) $order->grand_total,
                    'status' => $order->status,
                    'status_label' => $order->status_label ?? ucfirst(str_replace('_', ' ', $order->status)),
                    'payment_status' => $order->payment_status,
                    'payment_method' => $order->payment_method ?: 'ONLINE',
                    'date' => $order->created_at ? $order->created_at->format('M d, Y, h:i A') : '—',
                    'time_ago' => $timeAgo,
                ];
            };

            // =========================================================================
            // 4. Main Recent Orders: ONLY Payment Paid
            // =========================================================================
            $recentPaidOrders = Order::with('user')
                ->where('payment_status', 'paid')
                ->orderBy('created_at', 'desc')
                ->take(7)
                ->get()
                ->map($formatOrderRow);

            // =========================================================================
            // 5. SEPARATE SECTION: Payment Pending, Failed, Refunded (With Date Filtering)
            // =========================================================================
            $pendingQuery = Order::where('payment_status', 'pending');
            $failedQuery = Order::where('payment_status', 'failed');
            $refundedQuery = Order::whereIn('payment_status', ['refunded', 'partial_refunded']);

            $exceptionDateFrom = $request->input('exception_date_from', $request->input('date_from'));
            $exceptionDateTo = $request->input('exception_date_to', $request->input('date_to'));

            if (!empty($exceptionDateFrom)) {
                $pendingQuery->whereDate('created_at', '>=', $exceptionDateFrom);
                $failedQuery->whereDate('created_at', '>=', $exceptionDateFrom);
                $refundedQuery->whereDate('created_at', '>=', $exceptionDateFrom);
            }

            if (!empty($exceptionDateTo)) {
                $pendingQuery->whereDate('created_at', '<=', $exceptionDateTo);
                $failedQuery->whereDate('created_at', '<=', $exceptionDateTo);
                $refundedQuery->whereDate('created_at', '<=', $exceptionDateTo);
            }

            $paymentExceptions = [
                'date_from' => $exceptionDateFrom,
                'date_to' => $exceptionDateTo,
                'summary' => [
                    'pending' => [
                        'count' => (clone $pendingQuery)->count(),
                        'amount' => round((float) (clone $pendingQuery)->sum('grand_total'), 2),
                    ],
                    'failed' => [
                        'count' => (clone $failedQuery)->count(),
                        'amount' => round((float) (clone $failedQuery)->sum('grand_total'), 2),
                    ],
                    'refunded' => [
                        'count' => (clone $refundedQuery)->count(),
                        'amount' => round((float) (clone $refundedQuery)->sum('grand_total'), 2),
                    ],
                ],
                'pending_orders' => (clone $pendingQuery)->with('user')
                    ->orderBy('created_at', 'desc')
                    ->take(12)
                    ->get()
                    ->map($formatOrderRow),
                'failed_orders' => (clone $failedQuery)->with('user')
                    ->orderBy('created_at', 'desc')
                    ->take(12)
                    ->get()
                    ->map($formatOrderRow),
                'refunded_orders' => (clone $refundedQuery)->with('user')
                    ->orderBy('created_at', 'desc')
                    ->take(12)
                    ->get()
                    ->map($formatOrderRow),
            ];

            return response()->json([
                'success' => true,
                'message' => 'Dashboard statistics loaded successfully',
                'data' => [
                    'kpis' => [
                        'total_revenue' => round($totalRevenue, 2),
                        'total_orders' => $totalOrdersCount,
                        'today_revenue' => round($todayRevenue, 2),
                        'today_orders' => $todayOrdersCount,
                        'month_revenue' => round($monthRevenue, 2),
                        'month_orders' => $monthOrdersCount,
                        'active_products' => $totalProductsCount,
                        'total_customers' => $totalCustomersCount,
                        'low_stock_count' => $totalLowStockCount,
                    ],
                    'pipeline' => $pipeline,
                    'low_stock_alerts' => $lowStockVariants,
                    'recent_orders' => $recentPaidOrders,
                    'payment_exceptions' => $paymentExceptions,
                ]
            ]);
        } catch (\Exception $e) {
            Log::error('DashboardController@stats failed: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to load dashboard statistics: ' . $e->getMessage(),
                'error_code' => 'SERVER_ERROR'
            ], 500);
        }
    }
}
