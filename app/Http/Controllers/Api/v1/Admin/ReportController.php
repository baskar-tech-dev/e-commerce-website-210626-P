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
}
