<?php

namespace App\Repositories;

use App\Models\InventoryLedger;
use App\Models\ProductVariant;
use Illuminate\Pagination\LengthAwarePaginator;

class InventoryRepository implements InventoryRepositoryInterface
{
    /**
     * Get paginated variant stock items.
     */
    public function allVariants(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        $query = ProductVariant::with(['product.category']);

        if (isset($filters['category_id']) && $filters['category_id'] !== '') {
            $query->whereHas('product', function ($q) use ($filters) {
                $q->where('category_id', $filters['category_id']);
            });
        }

        if (!empty($filters['search'])) {
            $search = $filters['search'];
            $query->where(function ($q) use ($search) {
                $q->where('sku', 'like', "%{$search}%")
                  ->orWhere('barcode', 'like', "%{$search}%")
                  ->orWhereHas('product', function ($sub) use ($search) {
                      $sub->where('name', 'like', "%{$search}%");
                  });
            });
        }

        if (isset($filters['status']) && $filters['status'] !== '') {
            switch ($filters['status']) {
                case 'ok':
                    $query->whereColumn('stock_quantity', '>', 'low_stock_threshold');
                    break;
                case 'low':
                    $query->where('stock_quantity', '>', 0)
                          ->whereColumn('stock_quantity', '<=', 'low_stock_threshold');
                    break;
                case 'out_of_stock':
                    $query->where('stock_quantity', '=', 0);
                    break;
            }
        }

        return $query->orderBy('stock_quantity', 'asc')
            ->orderBy('sku', 'asc')
            ->paginate($perPage);
    }

    /**
     * Get paginated ledger movement history.
     */
    public function ledgerHistory(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        $query = InventoryLedger::with(['variant.product', 'creator']);

        if (isset($filters['product_variant_id']) && $filters['product_variant_id'] !== '') {
            $query->where('product_variant_id', $filters['product_variant_id']);
        }

        if (isset($filters['type']) && $filters['type'] !== '') {
            $query->where('type', $filters['type']);
        }

        if (isset($filters['direction']) && $filters['direction'] !== '') {
            $query->where('direction', $filters['direction']);
        }

        if (isset($filters['date_from']) && $filters['date_from'] !== '') {
            $query->where('created_at', '>=', $filters['date_from'] . ' 00:00:00');
        }

        if (isset($filters['date_to']) && $filters['date_to'] !== '') {
            $query->where('created_at', '<=', $filters['date_to'] . ' 23:59:59');
        }

        return $query->orderBy('created_at', 'desc')
            ->orderBy('id', 'desc')
            ->paginate($perPage);
    }

    /**
     * Find a variant with product details.
     */
    public function findVariant(int $variantId): ?ProductVariant
    {
        return ProductVariant::with(['product.category'])->find($variantId);
    }

    /**
     * Get stock overview KPI summary statistics.
     */
    public function stockOverviewStats(array $filters = []): array
    {
        $totalProducts = \App\Models\Product::where('is_active', true)->count();
        
        $variantStats = ProductVariant::whereHas('product', function ($q) {
            $q->where('is_active', true);
        })->selectRaw('
            COALESCE(SUM(stock_quantity), 0) as total_stock,
            COALESCE(SUM(reserved_quantity), 0) as total_reserved
        ')->first();

        $activeOrderQty = \App\Models\OrderItem::whereHas('order', function ($q) {
            $q->whereIn('status', [
                \App\Models\Order::STATUS_ORDER_PLACED,
                \App\Models\Order::STATUS_ORDER_CONFIRMED,
                \App\Models\Order::STATUS_PROCESSING,
                \App\Models\Order::STATUS_READY_TO_SHIP,
            ]);
        })->sum('quantity');

        $totalStock = (int) ($variantStats->total_stock ?? 0);
        $totalOrder = max((int) ($variantStats->total_reserved ?? 0), (int) $activeOrderQty);
        $totalAvail = max(0, $totalStock - $totalOrder);

        $lowStockItems = ProductVariant::whereHas('product', function ($q) {
            $q->where('is_active', true);
        })->whereRaw('(stock_quantity - reserved_quantity) > 0')
          ->whereRaw('(stock_quantity - reserved_quantity) <= low_stock_threshold')
          ->count();

        $outOfStockItems = ProductVariant::whereHas('product', function ($q) {
            $q->where('is_active', true);
        })->whereRaw('(stock_quantity - reserved_quantity) <= 0')
          ->count();

        return [
            'total_products' => $totalProducts,
            'total_stock_qty' => $totalStock,
            'total_order_qty' => $totalOrder,
            'total_available_qty' => $totalAvail,
            'low_stock_items' => $lowStockItems,
            'out_of_stock_items' => $outOfStockItems,
        ];
    }

    /**
     * Get paginated product stock overview records.
     */
    public function stockOverview(array $filters = [], int $perPage = 24): LengthAwarePaginator
    {
        $perPage = ($perPage > 0) ? min(max($perPage, 1), 5000) : 24;
        $page = isset($filters['page']) && is_numeric($filters['page']) && (int)$filters['page'] > 0 ? (int)$filters['page'] : 1;

        $query = \App\Models\Product::with([
            'category:id,name',
            'images' => function ($q) {
                $q->orderByDesc('is_primary')->orderBy('sort_order');
            },
            'variants' => function ($q) {
                $q->orderBy('sort_order')->orderBy('id');
            }
        ]);

        if (!empty($filters['search'])) {
            $search = trim($filters['search']);
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%")
                  ->orWhereHas('category', function ($catQ) use ($search) {
                      $catQ->where('name', 'like', "%{$search}%");
                  })
                  ->orWhereHas('variants', function ($vQ) use ($search) {
                      $vQ->where('sku', 'like', "%{$search}%")
                         ->orWhere('barcode', 'like', "%{$search}%")
                         ->orWhere('color', 'like', "%{$search}%")
                         ->orWhere('size', 'like', "%{$search}%");
                  });
            });
        }

        if (!empty($filters['category_id']) && is_numeric($filters['category_id']) && (int)$filters['category_id'] > 0) {
            $catId = (int)$filters['category_id'];
            $childIds = \App\Models\Category::where('parent_id', $catId)->pluck('id')->push($catId)->toArray();
            $query->whereIn('category_id', $childIds);
        }

        if (!empty($filters['status']) && $filters['status'] !== 'all') {
            $status = $filters['status'];
            if ($status === 'in_stock') {
                $query->whereHas('variants', function ($vQ) {
                    $vQ->whereRaw('(stock_quantity - reserved_quantity) > low_stock_threshold');
                });
            } elseif ($status === 'low_stock') {
                $query->whereHas('variants', function ($vQ) {
                    $vQ->whereRaw('(stock_quantity - reserved_quantity) > 0')
                       ->whereRaw('(stock_quantity - reserved_quantity) <= low_stock_threshold');
                });
            } elseif ($status === 'out_of_stock') {
                $query->whereDoesntHave('variants', function ($vQ) {
                    $vQ->whereRaw('(stock_quantity - reserved_quantity) > 0');
                });
            } elseif ($status === 'ordered') {
                $query->whereHas('variants', function ($vQ) {
                    $vQ->where('reserved_quantity', '>', 0);
                });
            }
        }

        if (!empty($filters['color'])) {
            $color = trim($filters['color']);
            $query->whereHas('variants', function ($vQ) use ($color) {
                $vQ->where('color', $color)
                   ->orWhere('color_code', $color);
            });
        }

        $sortBy = $filters['sort_by'] ?? 'name_asc';
        switch ($sortBy) {
            case 'name_desc':
                $query->orderBy('name', 'desc');
                break;
            case 'stock_high_low':
                $query->withSum('variants', 'stock_quantity')->orderBy('variants_sum_stock_quantity', 'desc');
                break;
            case 'stock_low_high':
                $query->withSum('variants', 'stock_quantity')->orderBy('variants_sum_stock_quantity', 'asc');
                break;
            case 'order_high_low':
                $query->withSum('variants', 'reserved_quantity')->orderBy('variants_sum_reserved_quantity', 'desc');
                break;
            case 'recent':
                $query->orderBy('updated_at', 'desc');
                break;
            case 'name_asc':
            default:
                $query->orderBy('name', 'asc');
                break;
        }

        $paginator = $query->paginate($perPage, ['*'], 'page', $page);

        $paginator->getCollection()->transform(function ($product) {
            $variants = $product->variants;
            $totalStock = (int) $variants->sum('stock_quantity');
            $totalOrder = (int) $variants->sum('reserved_quantity');
            $totalAvail = max(0, $totalStock - $totalOrder);

            $primaryImage = $product->images->firstWhere('is_primary', true) ?: $product->images->first();
            $primarySku = $variants->first()?->sku ?? 'SKU-NONE';

            $status = 'in_stock';
            $statusLabel = 'In Stock';

            if ($totalAvail <= 0) {
                $status = 'out_of_stock';
                $statusLabel = 'Out of Stock';
            } elseif ($totalAvail <= (int) $variants->sum('low_stock_threshold')) {
                $status = 'low_stock';
                $statusLabel = 'Low Stock';
            }

            $formattedVariants = $variants->map(function ($v) {
                $vStock = (int) $v->stock_quantity;
                $vOrder = (int) $v->reserved_quantity;
                $vAvail = max(0, $vStock - $vOrder);
                $vStatus = 'in_stock';
                if ($vAvail <= 0) {
                    $vStatus = 'out_of_stock';
                } elseif ($vAvail <= (int) $v->low_stock_threshold) {
                    $vStatus = 'low_stock';
                }

                return [
                    'id' => $v->id,
                    'product_id' => $v->product_id,
                    'sku' => $v->sku,
                    'color' => $v->color ?: 'Standard',
                    'color_code' => $v->color_code ?: '#5B163A',
                    'size' => $v->size ?: 'Free Size',
                    'stock_quantity' => $vStock,
                    'reserved_quantity' => $vOrder,
                    'order_qty' => $vOrder,
                    'avail_qty' => $vAvail,
                    'low_stock_threshold' => (int) $v->low_stock_threshold,
                    'selling_price' => (float) $v->selling_price,
                    'mrp' => (float) $v->mrp,
                    'status' => $vStatus,
                    'status_label' => ucfirst(str_replace('_', ' ', $vStatus)),
                ];
            });

            return [
                'id' => $product->id,
                'name' => $product->name,
                'slug' => $product->slug,
                'uuid' => $product->uuid,
                'sku' => $primarySku,
                'category_id' => $product->category_id,
                'category_name' => $product->category?->name ?? 'Uncategorized',
                'primary_image_url' => $primaryImage?->url ?? $primaryImage?->image_path ?? null,
                'stock_qty' => $totalStock,
                'order_qty' => $totalOrder,
                'avail_qty' => $totalAvail,
                'status' => $status,
                'status_label' => $statusLabel,
                'updated_at' => $product->updated_at?->toISOString(),
                'updated_at_formatted' => $product->updated_at ? $product->updated_at->format('d M h:i A') : '—',
                'variants_count' => $variants->count(),
                'variants' => $formattedVariants,
            ];
        });

        return $paginator;
    }
}
