<?php

namespace App\Http\Controllers\Api\v1\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\ProductVariant;
use App\Models\CustomerProfile;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;
use Exception;

class ReportController extends Controller
{
    /**
     * Get Sales & Revenue analytics report.
     */
    public function sales(Request $request): JsonResponse
    {
        try {
            $startDate = $request->input('start_date') 
                ? Carbon::parse($request->input('start_date'))->startOfDay() 
                : Carbon::now()->subDays(30)->startOfDay();
            
            $endDate = $request->input('end_date') 
                ? Carbon::parse($request->input('end_date'))->endOfDay() 
                : Carbon::now()->endOfDay();

            // Core aggregation queries
            $ordersQuery = Order::whereBetween('created_at', [$startDate, $endDate])
                ->where('status', '!=', 'cancelled');

            $revenue = (float) $ordersQuery->sum('grand_total');
            $ordersCount = $ordersQuery->count();
            $avgOrderValue = $ordersCount > 0 ? ($revenue / $ordersCount) : 0.0;

            $unitsSold = (int) OrderItem::whereHas('order', function ($q) use ($startDate, $endDate) {
                $q->whereBetween('created_at', [$startDate, $endDate])
                  ->where('status', '!=', 'cancelled');
            })->sum('quantity');

            // Daily Revenue Trend Line
            $trendData = Order::select(
                DB::raw('DATE(created_at) as date'),
                DB::raw('SUM(grand_total) as amount')
            )
            ->whereBetween('created_at', [$startDate, $endDate])
            ->where('status', '!=', 'cancelled')
            ->groupBy('date')
            ->orderBy('date', 'asc')
            ->get()
            ->map(function ($t) {
                return [
                    'date' => Carbon::parse($t->date)->format('M d'),
                    'amount' => (float) $t->amount,
                ];
            });

            // Sales by Category
            $categorySales = OrderItem::join('products', 'order_items.product_id', '=', 'products.id')
                ->join('categories', 'products.category_id', '=', 'categories.id')
                ->join('orders', 'order_items.order_id', '=', 'orders.id')
                ->select(
                    'categories.name as category_name',
                    DB::raw('SUM(order_items.total_price) as amount')
                )
                ->whereBetween('orders.created_at', [$startDate, $endDate])
                ->where('orders.status', '!=', 'cancelled')
                ->groupBy('categories.id', 'categories.name')
                ->orderBy('amount', 'desc')
                ->get()
                ->map(function ($c) {
                    return [
                        'category' => $c->category_name,
                        'amount' => (float) $c->amount,
                    ];
                });

            // Sales by Payment Method
            $paymentSales = Order::select(
                'payment_method',
                DB::raw('COUNT(*) as count'),
                DB::raw('SUM(grand_total) as amount')
            )
            ->whereBetween('created_at', [$startDate, $endDate])
            ->where('status', '!=', 'cancelled')
            ->groupBy('payment_method')
            ->get()
            ->map(function ($p) {
                return [
                    'method' => strtoupper($p->payment_method),
                    'count' => (int) $p->count,
                    'amount' => (float) $p->amount,
                ];
            });

            // Top Selling Products
            $topProducts = OrderItem::join('orders', 'order_items.order_id', '=', 'orders.id')
                ->select(
                    'order_items.product_name',
                    'order_items.sku',
                    DB::raw('SUM(order_items.quantity) as sold'),
                    DB::raw('SUM(order_items.total_price) as revenue')
                )
                ->whereBetween('orders.created_at', [$startDate, $endDate])
                ->where('orders.status', '!=', 'cancelled')
                ->groupBy('order_items.sku', 'order_items.product_name')
                ->orderBy('sold', 'desc')
                ->take(5)
                ->get()
                ->map(function ($p) {
                    return [
                        'name' => $p->product_name,
                        'sku' => $p->sku,
                        'sold' => (int) $p->sold,
                        'revenue' => (float) $p->revenue,
                    ];
                });

            return response()->json([
                'success' => true,
                'message' => 'Sales report loaded successfully',
                'data' => [
                    'kpis' => [
                        'revenue' => $revenue,
                        'orders' => $ordersCount,
                        'aov' => $avgOrderValue,
                        'units_sold' => $unitsSold,
                    ],
                    'revenue_trend' => $trendData,
                    'category_splits' => $categorySales,
                    'payment_splits' => $paymentSales,
                    'top_products' => $topProducts,
                ]
            ]);
        } catch (Exception $e) {
            Log::error('ReportController@sales failed: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to build sales report',
                'error_code' => 'SERVER_ERROR'
            ], 500);
        }
    }

    /**
     * Get Inventory valuation analytics report.
     */
    public function inventory(): JsonResponse
    {
        try {
            $totalVariants = ProductVariant::count();
            $lowStock = ProductVariant::where('stock_quantity', '<=', DB::raw('low_stock_threshold'))->count();
            $outOfStock = ProductVariant::where('stock_quantity', 0)->count();

            // Total stock value = sum(quantity * cost_price)
            $totalValuation = (float) ProductVariant::select(
                DB::raw('SUM(stock_quantity * COALESCE(cost_price, selling_price, 0)) as total')
            )->first()->total;

            // Low Stock Details list
            $lowStockItems = ProductVariant::with('product')
                ->where('stock_quantity', '<=', DB::raw('low_stock_threshold'))
                ->orderBy('stock_quantity', 'asc')
                ->take(10)
                ->get()
                ->map(function ($v) {
                    return [
                        'sku' => $v->sku,
                        'name' => ($v->product->name ?? 'Unknown') . ' (' . trim($v->size . ' ' . $v->color) . ')',
                        'stock' => $v->stock_quantity,
                        'threshold' => $v->low_stock_threshold,
                    ];
                });

            return response()->json([
                'success' => true,
                'message' => 'Inventory report loaded successfully',
                'data' => [
                    'kpis' => [
                        'total_variants' => $totalVariants,
                        'low_stock' => $lowStock,
                        'out_of_stock' => $outOfStock,
                        'valuation' => $totalValuation,
                    ],
                    'low_stock_details' => $lowStockItems,
                ]
            ]);
        } catch (Exception $e) {
            Log::error('ReportController@inventory failed: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to build inventory report',
                'error_code' => 'SERVER_ERROR'
            ], 500);
        }
    }

    /**
     * Get Customers growth analytics report.
     */
    public function customers(Request $request): JsonResponse
    {
        try {
            $totalCustomers = CustomerProfile::count();
            
            // New signups in last 30 days
            $newCustomers = CustomerProfile::where('created_at', '>=', Carbon::now()->subDays(30))->count();

            // Repeat purchase rate: customers with > 1 order / total customers
            $repeatCustomers = CustomerProfile::where('total_orders', '>', 1)->count();
            $repeatRate = $totalCustomers > 0 ? (($repeatCustomers / $totalCustomers) * 100) : 0.0;

            // Average LTV
            $averageLtv = (float) CustomerProfile::avg('total_spent') ?? 0.0;

            // Customer registrations timeline trend
            $registrationsTrend = CustomerProfile::select(
                DB::raw('DATE(created_at) as date'),
                DB::raw('COUNT(*) as count')
            )
            ->where('created_at', '>=', Carbon::now()->subDays(30))
            ->groupBy('date')
            ->orderBy('date', 'asc')
            ->get()
            ->map(function ($c) {
                return [
                    'date' => Carbon::parse($c->date)->format('M d'),
                    'count' => (int) $c->count,
                ];
            });

            return response()->json([
                'success' => true,
                'message' => 'Customer analytics report loaded successfully',
                'data' => [
                    'kpis' => [
                        'total_customers' => $totalCustomers,
                        'new_customers' => $newCustomers,
                        'repeat_rate' => $repeatRate,
                        'average_ltv' => $averageLtv,
                    ],
                    'registrations_trend' => $registrationsTrend,
                ]
            ]);
        } catch (Exception $e) {
            Log::error('ReportController@customers failed: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to build customer analytics report',
                'error_code' => 'SERVER_ERROR'
            ], 500);
        }
    }

    /**
     * Resolve date range from preset or custom dates.
     */
    protected function resolveDateRange(Request $request): array
    {
        $preset = $request->input('date_preset', 'this_month');
        $startDate = null;
        $endDate = null;

        switch ($preset) {
            case 'today':
                $startDate = Carbon::today()->startOfDay();
                $endDate = Carbon::today()->endOfDay();
                break;
            case 'yesterday':
                $startDate = Carbon::yesterday()->startOfDay();
                $endDate = Carbon::yesterday()->endOfDay();
                break;
            case 'this_week':
                $startDate = Carbon::now()->startOfWeek();
                $endDate = Carbon::now()->endOfWeek();
                break;
            case 'last_7_days':
                $startDate = Carbon::now()->subDays(6)->startOfDay();
                $endDate = Carbon::now()->endOfDay();
                break;
            case 'this_month':
                $startDate = Carbon::now()->startOfMonth()->startOfDay();
                $endDate = Carbon::now()->endOfMonth()->endOfDay();
                break;
            case 'last_month':
                $startDate = Carbon::now()->subMonth()->startOfMonth()->startOfDay();
                $endDate = Carbon::now()->subMonth()->endOfMonth()->endOfDay();
                break;
            case 'this_quarter':
                $startDate = Carbon::now()->startOfQuarter()->startOfDay();
                $endDate = Carbon::now()->endOfQuarter()->endOfDay();
                break;
            case 'this_year':
                $startDate = Carbon::now()->startOfYear()->startOfDay();
                $endDate = Carbon::now()->endOfYear()->endOfDay();
                break;
            case 'all_time':
                $startDate = null;
                $endDate = null;
                break;
            case 'custom':
            default:
                if ($request->filled('start_date')) {
                    $startDate = Carbon::parse($request->input('start_date'))->startOfDay();
                }
                if ($request->filled('end_date')) {
                    $endDate = Carbon::parse($request->input('end_date'))->endOfDay();
                }
                break;
        }

        return [$startDate, $endDate, $preset];
    }

    /**
     * Build shared sales statement query based on request filters.
     */
    protected function buildSalesStatementQuery(Request $request)
    {
        [$startDate, $endDate] = $this->resolveDateRange($request);

        $query = Order::with(['user', 'items.variant', 'courier']);

        if ($startDate && $endDate) {
            $query->whereBetween('created_at', [$startDate, $endDate]);
        } elseif ($startDate) {
            $query->where('created_at', '>=', $startDate);
        } elseif ($endDate) {
            $query->where('created_at', '<=', $endDate);
        }

        // Filter by Order Status
        if ($request->filled('order_status') && $request->input('order_status') !== 'all') {
            $orderStatus = strtolower(trim($request->input('order_status')));
            $query->where('status', $orderStatus);
        }

        // Filter by Payment Status
        if ($request->filled('payment_status') && $request->input('payment_status') !== 'all') {
            $paymentStatus = strtolower(trim($request->input('payment_status')));
            if (in_array($paymentStatus, ['paid', 'captured', 'success'])) {
                $query->whereIn('payment_status', ['paid', 'captured', 'success']);
            } else {
                $query->where('payment_status', $paymentStatus);
            }
        }

        // Filter by Payment Method
        if ($request->filled('payment_method') && $request->input('payment_method') !== 'all') {
            $method = strtolower(trim($request->input('payment_method')));
            $query->where('payment_method', 'like', "%{$method}%");
        }

        // Multi-field search
        if ($request->filled('search')) {
            $search = trim($request->input('search'));
            $query->where(function ($q) use ($search) {
                $q->where('order_number', 'like', "%{$search}%")
                  ->orWhere('shipping_first_name', 'like', "%{$search}%")
                  ->orWhere('shipping_last_name', 'like', "%{$search}%")
                  ->orWhere('shipping_phone', 'like', "%{$search}%")
                  ->orWhere('shipping_city', 'like', "%{$search}%")
                  ->orWhere('shipping_state', 'like', "%{$search}%")
                  ->orWhere('shipping_postal_code', 'like', "%{$search}%")
                  ->orWhere('tracking_number', 'like', "%{$search}%")
                  ->orWhere('gateway_order_id', 'like', "%{$search}%")
                  ->orWhere('gateway_payment_id', 'like', "%{$search}%")
                  ->orWhereHas('user', function ($uq) use ($search) {
                      $uq->where('name', 'like', "%{$search}%")
                         ->orWhere('email', 'like', "%{$search}%")
                         ->orWhere('phone', 'like', "%{$search}%");
                  })
                  ->orWhereHas('items', function ($iq) use ($search) {
                      $iq->where('product_name', 'like', "%{$search}%")
                         ->orWhere('sku', 'like', "%{$search}%");
                  });
            });
        }

        return $query;
    }

    /**
     * Get Comprehensive Sales Statement Report with metrics and detailed line items.
     *
     * GET /api/admin/reports/sales-statement
     */
    public function salesStatement(Request $request): JsonResponse
    {
        try {
            [$startDate, $endDate, $preset] = $this->resolveDateRange($request);
            $query = $this->buildSalesStatementQuery($request);

            // Compute Statement-level Aggregate Metrics
            $aggQuery = (clone $query);
            $metrics = $aggQuery->select(
                DB::raw('COUNT(*) as total_orders'),
                DB::raw('COALESCE(SUM(total_items), 0) as total_units_sold'),
                DB::raw('COALESCE(SUM(subtotal), 0) as net_sales'),
                DB::raw('COALESCE(SUM(discount_amount), 0) as total_discounts'),
                DB::raw('COALESCE(SUM(tax_amount), 0) as total_tax'),
                DB::raw('COALESCE(SUM(cgst_amount), 0) as total_cgst'),
                DB::raw('COALESCE(SUM(sgst_amount), 0) as total_sgst'),
                DB::raw('COALESCE(SUM(igst_amount), 0) as total_igst'),
                DB::raw('COALESCE(SUM(shipping_amount), 0) as total_shipping'),
                DB::raw('COALESCE(SUM(grand_total), 0) as grand_total'),
                DB::raw("COALESCE(SUM(CASE WHEN payment_status IN ('paid', 'captured', 'success') THEN grand_total ELSE 0 END), 0) as paid_revenue"),
                DB::raw("COUNT(CASE WHEN payment_status IN ('paid', 'captured', 'success') THEN 1 ELSE NULL END) as paid_count"),
                DB::raw("COALESCE(SUM(CASE WHEN payment_status = 'pending' THEN grand_total ELSE 0 END), 0) as pending_revenue"),
                DB::raw("COUNT(CASE WHEN payment_status = 'pending' THEN 1 ELSE NULL END) as pending_count"),
                DB::raw("COALESCE(SUM(CASE WHEN status = 'cancelled' THEN grand_total ELSE 0 END), 0) as cancelled_revenue"),
                DB::raw("COUNT(CASE WHEN status = 'cancelled' THEN 1 ELSE NULL END) as cancelled_count")
            )->first();

            $totalOrders = (int) ($metrics->total_orders ?? 0);
            $grandTotal = (float) ($metrics->grand_total ?? 0.0);
            $netSales = (float) ($metrics->net_sales ?? 0.0);
            $totalDiscounts = (float) ($metrics->total_discounts ?? 0.0);
            $grossSales = $netSales + $totalDiscounts;
            $aov = $totalOrders > 0 ? round($grandTotal / $totalOrders, 2) : 0.0;

            // Grouped Status Breakdown
            $statusBreakdown = (clone $query)->select('status', DB::raw('COUNT(*) as count'), DB::raw('COALESCE(SUM(grand_total), 0) as amount'))
                ->groupBy('status')
                ->get()
                ->map(fn($s) => [
                    'status' => $s->status,
                    'count' => (int) $s->count,
                    'amount' => (float) $s->amount,
                ]);

            // Grouped Payment Method Breakdown
            $paymentBreakdown = (clone $query)->select('payment_method', DB::raw('COUNT(*) as count'), DB::raw('COALESCE(SUM(grand_total), 0) as amount'))
                ->groupBy('payment_method')
                ->get()
                ->map(fn($p) => [
                    'method' => strtoupper($p->payment_method ?: 'ONLINE'),
                    'count' => (int) $p->count,
                    'amount' => (float) $p->amount,
                ]);

            // Sorting
            $sortBy = $request->input('sort_by', 'date_desc');
            switch ($sortBy) {
                case 'date_asc':
                    $query->orderBy('created_at', 'asc');
                    break;
                case 'amount_desc':
                    $query->orderBy('grand_total', 'desc');
                    break;
                case 'amount_asc':
                    $query->orderBy('grand_total', 'asc');
                    break;
                case 'items_desc':
                    $query->orderBy('total_items', 'desc');
                    break;
                case 'date_desc':
                default:
                    $query->orderBy('created_at', 'desc');
                    break;
            }

            // Pagination
            $isAll = $request->input('per_page') === 'all';
            if ($isAll) {
                $orders = $query->limit(2000)->get();
                $paginationMeta = [
                    'current_page' => 1,
                    'last_page' => 1,
                    'per_page' => $orders->count(),
                    'total' => $orders->count(),
                ];
            } else {
                $perPage = min(max((int) $request->input('per_page', 20), 5), 100);
                $p = $query->paginate($perPage);
                $orders = collect($p->items());
                $paginationMeta = [
                    'current_page' => $p->currentPage(),
                    'last_page' => $p->lastPage(),
                    'per_page' => $p->perPage(),
                    'total' => $p->total(),
                ];
            }

            // Transform each order to detailed statement representation
            $transformedOrders = $orders->map(function ($order) {
                $customerName = trim(($order->shipping_first_name ?? '') . ' ' . ($order->shipping_last_name ?? ''));
                if (empty($customerName)) {
                    $customerName = $order->user?->name ?? 'Customer';
                }

                $fullAddress = trim(implode(', ', array_filter([
                    $order->shipping_address_line_1,
                    $order->shipping_address_line_2,
                    $order->shipping_city,
                    $order->shipping_state,
                    $order->shipping_postal_code,
                ])));

                $items = $order->items->map(function ($item) {
                    return [
                        'id' => $item->id,
                        'product_id' => $item->product_id,
                        'product_name' => $item->product_name,
                        'variant_name' => $item->variant_name,
                        'sku' => $item->sku,
                        'quantity' => (int) $item->quantity,
                        'unit_mrp' => (float) ($item->unit_mrp ?: $item->unit_price),
                        'unit_price' => (float) $item->unit_price,
                        'discount' => (float) $item->discount,
                        'tax_amount' => (float) $item->tax_amount,
                        'tax_rate' => (float) $item->tax_rate,
                        'total_price' => (float) $item->total_price,
                        'image_url' => $item->image_url,
                    ];
                });

                $itemsGrossMrp = (float) $items->sum(fn($i) => $i['unit_mrp'] * $i['quantity']);

                return [
                    'id' => $order->id,
                    'uuid' => $order->uuid,
                    'order_number' => $order->order_number,
                    'created_at' => $order->created_at?->format('Y-m-d H:i:s'),
                    'created_at_display' => $order->created_at?->format('M d, Y h:i A'),
                    'status' => $order->status,
                    'status_details' => $order->status_details,
                    'status_label' => $order->status_label,
                    'payment_status' => $order->payment_status,
                    'payment_method' => strtoupper($order->payment_method ?: 'ONLINE'),
                    'paid_at' => $order->paid_at?->format('M d, Y h:i A'),
                    'gateway_order_id' => $order->gateway_order_id,
                    'gateway_payment_id' => $order->gateway_payment_id,

                    'customer' => [
                        'name' => $customerName,
                        'email' => $order->user?->email ?? '—',
                        'phone' => $order->shipping_phone ?? ($order->user?->phone ?? '—'),
                        'city' => $order->shipping_city ?? '—',
                        'state' => $order->shipping_state ?? '—',
                        'postal_code' => $order->shipping_postal_code ?? '—',
                        'full_address' => $fullAddress,
                    ],

                    'financials' => [
                        'gross_mrp' => $itemsGrossMrp ?: (float) $order->subtotal,
                        'subtotal' => (float) $order->subtotal,
                        'discount_amount' => (float) $order->discount_amount,
                        'tax_amount' => (float) $order->tax_amount,
                        'cgst_amount' => (float) $order->cgst_amount,
                        'sgst_amount' => (float) $order->sgst_amount,
                        'igst_amount' => (float) $order->igst_amount,
                        'shipping_amount' => (float) $order->shipping_amount,
                        'grand_total' => (float) $order->grand_total,
                        'total_items' => (int) $order->total_items,
                        'currency' => $order->currency ?: 'INR',
                    ],

                    'logistics' => [
                        'courier_name' => $order->courier_name ?? $order->courier?->name ?? '—',
                        'tracking_number' => $order->tracking_number ?? '—',
                        'tracking_url' => $order->tracking_url,
                        'shipping_method' => $order->shipping_method ?? 'Standard Delivery',
                        'shipped_at' => $order->shipped_at?->format('M d, Y'),
                        'delivered_at' => $order->delivered_at?->format('M d, Y'),
                    ],

                    'items' => $items,
                ];
            });

            return response()->json([
                'success' => true,
                'message' => 'Sales statement report loaded successfully',
                'summary' => [
                    'date_preset' => $preset,
                    'start_date' => $startDate?->format('Y-m-d'),
                    'end_date' => $endDate?->format('Y-m-d'),
                    'total_orders' => $totalOrders,
                    'total_units_sold' => (int) ($metrics->total_units_sold ?? 0),
                    'gross_sales' => round($grossSales, 2),
                    'total_discounts' => round($totalDiscounts, 2),
                    'net_sales' => round($netSales, 2),
                    'total_tax' => round((float) ($metrics->total_tax ?? 0), 2),
                    'total_cgst' => round((float) ($metrics->total_cgst ?? 0), 2),
                    'total_sgst' => round((float) ($metrics->total_sgst ?? 0), 2),
                    'total_igst' => round((float) ($metrics->total_igst ?? 0), 2),
                    'total_shipping' => round((float) ($metrics->total_shipping ?? 0), 2),
                    'grand_total' => round($grandTotal, 2),
                    'average_order_value' => $aov,
                    'paid_revenue' => round((float) ($metrics->paid_revenue ?? 0), 2),
                    'paid_count' => (int) ($metrics->paid_count ?? 0),
                    'pending_revenue' => round((float) ($metrics->pending_revenue ?? 0), 2),
                    'pending_count' => (int) ($metrics->pending_count ?? 0),
                    'cancelled_revenue' => round((float) ($metrics->cancelled_revenue ?? 0), 2),
                    'cancelled_count' => (int) ($metrics->cancelled_count ?? 0),
                    'status_breakdown' => $statusBreakdown,
                    'payment_breakdown' => $paymentBreakdown,
                ],
                'data' => $transformedOrders,
                'meta' => $paginationMeta,
            ]);
        } catch (Exception $e) {
            Log::error('ReportController@salesStatement failed: ' . $e->getMessage() . ' ' . $e->getTraceAsString());
            return response()->json([
                'success' => false,
                'message' => 'Failed to build sales statement report: ' . $e->getMessage(),
                'error_code' => 'SERVER_ERROR'
            ], 500);
        }
    }

    /**
     * Export Sales Statement Report as CSV with UTF-8 BOM.
     *
     * GET /api/admin/reports/sales-statement/export
     */
    public function exportSalesStatement(Request $request)
    {
        try {
            $query = $this->buildSalesStatementQuery($request);
            $orders = $query->orderBy('created_at', 'desc')->get();

            $dateTag = date('Ymd_His');
            $filename = "Sales_Statement_{$dateTag}.csv";

            $headers = [
                'Content-Type' => 'text/csv; charset=UTF-8',
                'Content-Disposition' => "attachment; filename=\"{$filename}\"",
                'Pragma' => 'no-cache',
                'Cache-Control' => 'must-revalidate, post-check=0, pre-check=0',
                'Expires' => '0',
            ];

            $callback = function () use ($orders) {
                $file = fopen('php://output', 'w');
                // UTF-8 BOM so Microsoft Excel renders international characters and currency cleanly
                fputs($file, "\xEF\xBB\xBF");

                fputcsv($file, [
                    'S.No',
                    'Order Number',
                    'Date & Time',
                    'Customer Name',
                    'Customer Email',
                    'Customer Phone',
                    'Shipping City',
                    'Shipping State',
                    'Postal Code',
                    'Payment Method',
                    'Payment Status',
                    'Order Status',
                    'Total Units',
                    'Gross Sales (INR)',
                    'Discounts (INR)',
                    'Net Subtotal (INR)',
                    'CGST (INR)',
                    'SGST (INR)',
                    'IGST (INR)',
                    'Total GST (INR)',
                    'Shipping Fee (INR)',
                    'Grand Total (INR)',
                    'Courier Partner',
                    'Tracking Number',
                    'Itemized Products Breakdown'
                ]);

                $index = 1;
                foreach ($orders as $order) {
                    $customerName = trim(($order->shipping_first_name ?? '') . ' ' . ($order->shipping_last_name ?? ''));
                    if (empty($customerName)) {
                        $customerName = $order->user?->name ?? 'Customer';
                    }

                    $itemsBreakdown = $order->items->map(function ($item) {
                        return "{$item->product_name} [SKU: {$item->sku}, Qty: {$item->quantity}, Rate: {$item->unit_price}, Total: {$item->total_price}]";
                    })->implode(' | ');

                    $grossMrp = $order->items->sum(fn($i) => ($i->unit_mrp ?: $i->unit_price) * $i->quantity);

                    fputcsv($file, [
                        $index++,
                        $order->order_number,
                        $order->created_at?->format('Y-m-d H:i:s'),
                        $customerName,
                        $order->user?->email ?? '—',
                        $order->shipping_phone ?? ($order->user?->phone ?? '—'),
                        $order->shipping_city ?? '—',
                        $order->shipping_state ?? '—',
                        $order->shipping_postal_code ?? '—',
                        strtoupper($order->payment_method ?: 'ONLINE'),
                        ucfirst($order->payment_status),
                        $order->status_label,
                        $order->total_items,
                        number_format((float) ($grossMrp ?: $order->subtotal), 2, '.', ''),
                        number_format((float) $order->discount_amount, 2, '.', ''),
                        number_format((float) $order->subtotal, 2, '.', ''),
                        number_format((float) $order->cgst_amount, 2, '.', ''),
                        number_format((float) $order->sgst_amount, 2, '.', ''),
                        number_format((float) $order->igst_amount, 2, '.', ''),
                        number_format((float) $order->tax_amount, 2, '.', ''),
                        number_format((float) $order->shipping_amount, 2, '.', ''),
                        number_format((float) $order->grand_total, 2, '.', ''),
                        $order->courier_name ?? $order->courier?->name ?? '—',
                        $order->tracking_number ?? '—',
                        $itemsBreakdown
                    ]);
                }

                fclose($file);
            };

            return response()->stream($callback, 200, $headers);
        } catch (Exception $e) {
            Log::error('ReportController@exportSalesStatement failed: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to export sales statement: ' . $e->getMessage()
            ], 500);
        }
    }
}

