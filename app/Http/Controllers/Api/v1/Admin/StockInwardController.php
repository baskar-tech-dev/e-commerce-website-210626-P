<?php

namespace App\Http\Controllers\Api\v1\Admin;

use App\Http\Controllers\Controller;
use App\Models\StockInward;
use App\Models\StockInwardItem;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\Category;
use App\Models\Factory;
use App\Models\Color;
use App\Models\Size;
use App\Models\InventoryLedger;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Carbon\Carbon;
use Exception;

class StockInwardController extends Controller
{
    /**
     * Display a listing of stock inward records.
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $query = StockInward::with(['creator:id,name', 'category:id,name', 'factory:id,name,code', 'items.product:id,name'])
                ->withCount('items')
                ->orderBy('inward_date', 'desc')
                ->orderBy('id', 'desc');

            if ($request->filled('search')) {
                $search = $request->input('search');
                $query->where(function ($q) use ($search) {
                    $q->where('inward_number', 'like', "%{$search}%")
                      ->orWhere('supplier_name', 'like', "%{$search}%")
                      ->orWhere('reference_no', 'like', "%{$search}%")
                      ->orWhere('notes', 'like', "%{$search}%")
                      ->orWhereHas('factory', function ($fq) use ($search) {
                          $fq->where('name', 'like', "%{$search}%")
                             ->orWhere('code', 'like', "%{$search}%");
                      });
                });
            }

            if ($request->filled('category_id')) {
                $query->where('category_id', $request->input('category_id'));
            }

            if ($request->filled('factory_id')) {
                $query->where('factory_id', $request->input('factory_id'));
            }

            if ($request->filled('date_from')) {
                $query->whereDate('inward_date', '>=', $request->input('date_from'));
            }

            if ($request->filled('date_to')) {
                $query->whereDate('inward_date', '<=', $request->input('date_to'));
            }

            $perPage = (int) $request->input('per_page', 15);
            $inwards = $query->paginate($perPage);

            return response()->json([
                'success' => true,
                'data' => $inwards->items(),
                'meta' => [
                    'current_page' => $inwards->currentPage(),
                    'last_page' => $inwards->lastPage(),
                    'per_page' => $inwards->perPage(),
                    'total' => $inwards->total(),
                ]
            ]);
        } catch (Exception $e) {
            Log::error('StockInwardController@index error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve stock inwards: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Display the specified stock inward with line items.
     */
    public function show(int $id): JsonResponse
    {
        try {
            $inward = StockInward::with([
                'creator:id,name,email',
                'category:id,name',
                'factory:id,name,code,city,phone',
                'items.product.images',
                'items.variant',
                'items.colorMaster'
            ])->findOrFail($id);

            return response()->json([
                'success' => true,
                'data' => $inward
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Stock inward record not found.'
            ], 404);
        }
    }

    /**
     * Store a newly created stock inward and increment product stock.
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'inward_number' => 'nullable|string|max:60|unique:stock_inwards,inward_number',
            'inward_date' => 'required|date',
            'category_id' => 'nullable|exists:categories,id',
            'factory_id' => 'nullable|exists:factories,id',
            'supplier_name' => 'nullable|string|max:150',
            'reference_no' => 'nullable|string|max:100',
            'notes' => 'nullable|string|max:1000',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.color' => 'nullable|string|max:80',
            'items.*.color_id' => 'nullable|exists:colors,id',
            'items.*.size' => 'required|string|max:50',
            'items.*.quantity' => 'required|integer|min:1',
            'items.*.unit_cost' => 'nullable|numeric|min:0',
        ]);

        return DB::transaction(function () use ($validated, $request) {
            $userId = auth()->id();
            
            // Resolve Factory & Supplier Name
            $factoryId = $validated['factory_id'] ?? null;
            $supplierName = $validated['supplier_name'] ?? null;
            if ($factoryId && empty($supplierName)) {
                $fac = Factory::find($factoryId);
                if ($fac) {
                    $supplierName = $fac->name;
                }
            }

            // Auto generate inward number if not provided
            $inwardNumber = $validated['inward_number'] ?? null;
            if (empty($inwardNumber)) {
                $dateStr = Carbon::parse($validated['inward_date'])->format('Ymd');
                $countToday = StockInward::whereDate('inward_date', $validated['inward_date'])->count() + 1;
                $inwardNumber = 'INW-' . $dateStr . '-' . str_pad($countToday, 4, '0', STR_PAD_LEFT);
            }

            // Compute totals
            $totalItems = count($validated['items']);
            $totalQuantity = 0;
            $totalAmount = 0.00;

            foreach ($validated['items'] as $item) {
                $qty = (int) $item['quantity'];
                $cost = (float) ($item['unit_cost'] ?? 0);
                $totalQuantity += $qty;
                $totalAmount += ($qty * $cost);
            }

            // Create Inward Master
            $inward = StockInward::create([
                'inward_number' => $inwardNumber,
                'inward_date' => $validated['inward_date'],
                'category_id' => $validated['category_id'] ?? null,
                'factory_id' => $factoryId,
                'supplier_name' => $supplierName,
                'reference_no' => $validated['reference_no'] ?? null,
                'status' => 'completed',
                'total_items' => $totalItems,
                'total_quantity' => $totalQuantity,
                'total_amount' => $totalAmount,
                'notes' => $validated['notes'] ?? null,
                'created_by' => $userId,
            ]);

            // Process each item and adjust stock
            foreach ($validated['items'] as $item) {
                $product = Product::with('variants')->findOrFail($item['product_id']);
                $qty = (int) $item['quantity'];
                $unitCost = (float) ($item['unit_cost'] ?? 0);
                $subtotal = $qty * $unitCost;

                $colorName = trim($item['color'] ?? '');
                $colorId = $item['color_id'] ?? null;
                $sizeName = trim($item['size']);

                // Find color name if color_id was passed
                if ($colorId && empty($colorName)) {
                    $cObj = Color::find($colorId);
                    if ($cObj) {
                        $colorName = $cObj->name;
                    }
                }

                // 1. Locate or create ProductVariant
                $variantQuery = ProductVariant::where('product_id', $product->id);
                
                if (!empty($colorName)) {
                    $variantQuery->where('color', $colorName);
                }
                if (!empty($sizeName)) {
                    $variantQuery->where('size', $sizeName);
                }

                $variant = $variantQuery->first();

                if (!$variant) {
                    // Try fallback by size only if color wasn't specified
                    if (empty($colorName) && !empty($sizeName)) {
                        $variant = ProductVariant::where('product_id', $product->id)
                            ->where('size', $sizeName)
                            ->first();
                    }
                }

                if ($variant) {
                    $stockBefore = (int) $variant->stock_quantity;
                    $variant->stock_quantity = $stockBefore + $qty;
                    if ($unitCost > 0) {
                        $variant->cost_price = $unitCost;
                    }
                    $variant->save();
                    $stockAfter = $variant->stock_quantity;
                } else {
                    // Create new variant
                    $skuSuffix = strtoupper(Str::slug(($colorName ? $colorName . '-' : '') . ($sizeName ?: 'STD')));
                    $generatedSku = ($product->sku ?: 'PROD-' . $product->id) . '-' . $skuSuffix;

                    // Ensure unique SKU
                    $uniqueSku = $generatedSku;
                    $skuCounter = 1;
                    while (ProductVariant::where('sku', $uniqueSku)->exists()) {
                        $uniqueSku = $generatedSku . '-' . $skuCounter++;
                    }

                    $stockBefore = 0;
                    $variant = ProductVariant::create([
                        'product_id' => $product->id,
                        'sku' => $uniqueSku,
                        'size' => $sizeName,
                        'color' => $colorName ?: null,
                        'mrp' => $product->mrp ?? $product->price ?? 0,
                        'selling_price' => $product->selling_price ?? $product->price ?? 0,
                        'cost_price' => $unitCost > 0 ? $unitCost : ($product->cost_price ?? 0),
                        'stock_quantity' => $qty,
                        'reserved_quantity' => 0,
                        'low_stock_threshold' => 5,
                        'is_active' => true,
                    ]);
                    $stockAfter = $variant->stock_quantity;
                }

                // 2. Create Stock Inward Item row
                StockInwardItem::create([
                    'stock_inward_id' => $inward->id,
                    'product_id' => $product->id,
                    'product_variant_id' => $variant->id,
                    'color' => $colorName ?: null,
                    'color_id' => $colorId ?: null,
                    'size' => $sizeName,
                    'sku' => $variant->sku,
                    'quantity' => $qty,
                    'unit_cost' => $unitCost,
                    'subtotal' => $subtotal,
                ]);

                // 3. Record in InventoryLedger
                try {
                    InventoryLedger::create([
                        'product_variant_id' => $variant->id,
                        'type' => 'inward',
                        'direction' => 'in',
                        'quantity' => $qty,
                        'unit_cost' => $unitCost,
                        'reference_type' => StockInward::class,
                        'reference_id' => $inward->id,
                        'stock_before' => $stockBefore,
                        'stock_after' => $stockAfter,
                        'notes' => "Stock Inward #{$inward->inward_number} - Added {$qty} units",
                        'created_by' => $userId,
                    ]);
                } catch (Exception $le) {
                    Log::warning('InventoryLedger logging skipped: ' . $le->getMessage());
                }

                // 4. Update overall product timestamp
                $product->touch();
            }

            return response()->json([
                'success' => true,
                'message' => "Stock Inward #{$inward->inward_number} recorded successfully and stock updated.",
                'data' => $inward->load('items.product', 'items.variant', 'category', 'factory')
            ], 201);
        });
    }

    /**
     * Delete a stock inward record.
     */
    public function destroy(int $id): JsonResponse
    {
        try {
            $inward = StockInward::findOrFail($id);
            $inward->delete();

            return response()->json([
                'success' => true,
                'message' => 'Stock inward record deleted successfully.'
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete stock inward record: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Helper endpoint returning categories, factories, products with their colors and sizes for Inward Form.
     */
    public function formData(): JsonResponse
    {
        try {
            // Active Categories
            $categories = Category::where('is_active', true)
                ->select('id', 'name', 'slug')
                ->orderBy('name', 'asc')
                ->get();

            // Active Factories / Weaving Units
            $factories = Factory::where('is_active', true)
                ->select('id', 'name', 'code', 'city', 'phone')
                ->orderBy('name', 'asc')
                ->get();

            // Active Products with pre-grouped available colors and sizes
            $products = Product::where('is_active', true)
                ->with([
                    'variants:id,product_id,sku,size,color,cost_price,selling_price,stock_quantity', 
                    'images:id,product_id,url,is_primary'
                ])
                ->select('id', 'category_id', 'name', 'slug', 'selling_price', 'mrp', 'cost_price')
                ->orderBy('name', 'asc')
                ->get()
                ->map(function ($p) {
                    $totalStock = $p->variants->sum('stock_quantity');
                    $p->stock_quantity = $totalStock;
                    $p->price = $p->selling_price;
                    
                    // Available unique colors for this product
                    $p->available_colors = $p->variants
                        ->pluck('color')
                        ->filter(fn($c) => !empty($c))
                        ->unique()
                        ->values()
                        ->toArray();

                    // Available unique sizes for this product
                    $p->available_sizes = $p->variants
                        ->pluck('size')
                        ->filter(fn($s) => !empty($s))
                        ->unique()
                        ->values()
                        ->toArray();

                    // Fallback to default if no sizes configured
                    if (empty($p->available_sizes)) {
                        $p->available_sizes = ['Free Size'];
                    }

                    return $p;
                });

            $colors = Color::where('is_active', true)->orderBy('name', 'asc')->get();
            $sizes = Size::where('is_active', true)->orderBy('sort_order', 'asc')->orderBy('name', 'asc')->get();

            // Next Inward Number preview
            $dateStr = Carbon::today()->format('Ymd');
            $countToday = StockInward::whereDate('inward_date', Carbon::today())->count() + 1;
            $nextInwardNumber = 'INW-' . $dateStr . '-' . str_pad($countToday, 4, '0', STR_PAD_LEFT);

            return response()->json([
                'success' => true,
                'data' => [
                    'categories' => $categories,
                    'factories' => $factories,
                    'products' => $products,
                    'colors' => $colors,
                    'sizes' => $sizes,
                    'next_inward_number' => $nextInwardNumber,
                    'today' => Carbon::today()->format('Y-m-d'),
                ]
            ]);
        } catch (Exception $e) {
            Log::error('StockInwardController@formData error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to load inward form metadata: ' . $e->getMessage()
            ], 500);
        }
    }
}
