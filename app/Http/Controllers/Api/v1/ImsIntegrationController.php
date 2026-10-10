<?php

namespace App\Http\Controllers\Api\v1;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\InventoryLedger;
use App\Models\Product;
use App\Models\ProductImage;
use App\Models\ProductVariant;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class ImsIntegrationController extends Controller
{
    /**
     * Optional API key security check.
     */
    protected function checkApiKey(Request $request): ?JsonResponse
    {
        $expectedKey = config('services.ims.api_key') ?? env('IMS_API_KEY');
        if ($expectedKey) {
            $key = $request->header('X-IMS-KEY') ?: $request->bearerToken();
            if ($key !== $expectedKey) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized IMS integration request',
                ], 401);
            }
        }
        return null;
    }

    /**
     * List active product categories for IMS filtering.
     */
    public function categories(Request $request): JsonResponse
    {
        if ($authError = $this->checkApiKey($request)) {
            return $authError;
        }

        $categories = Category::whereNull('deleted_at')
            ->select('id', 'name', 'slug')
            ->orderBy('name')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $categories,
        ]);
    }

    /**
     * List products with their variants for IMS import/browsing.
     */
    public function index(Request $request): JsonResponse
    {
        if ($authError = $this->checkApiKey($request)) {
            return $authError;
        }

        try {
            $query = Product::with([
                'category:id,name,slug',
                'variants' => function ($vq) {
                    $vq->orderBy('sort_order')->orderBy('id');
                },
                'images' => function ($iq) {
                    $iq->orderBy('sort_order')->orderBy('id');
                },
            ]);

            // Search filter by product name, slug, or variant SKU
            if ($search = $request->query('search')) {
                $query->where(function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                      ->orWhere('slug', 'like', "%{$search}%")
                      ->orWhereHas('variants', function ($vq) use ($search) {
                          $vq->where('sku', 'like', "%{$search}%");
                      });
                });
            }

            // SKU filter directly
            if ($sku = $request->query('sku')) {
                $query->whereHas('variants', function ($vq) use ($sku) {
                    $vq->where('sku', 'like', "%{$sku}%");
                });
            }

            // Category filter (by ID, or by name/slug)
            if ($categoryId = $request->query('category_id')) {
                $query->where('category_id', $categoryId);
            } elseif ($cat = $request->query('category')) {
                $query->whereHas('category', function ($cq) use ($cat) {
                    $cq->where('name', $cat)->orWhere('slug', $cat);
                });
            }

            // Status filter
            if ($request->has('is_active')) {
                $query->where('is_active', filter_var($request->query('is_active'), FILTER_VALIDATE_BOOLEAN));
            }

            $query->orderBy('id', 'desc');

            $all = filter_var($request->query('all', false), FILTER_VALIDATE_BOOLEAN);
            if ($all) {
                $products = $query->get();
                $formatted = $products->map(fn ($p) => $this->formatProduct($p));
                return response()->json([
                    'success' => true,
                    'count' => $formatted->count(),
                    'data' => $formatted,
                ]);
            }

            $perPage = (int) $request->query('per_page', 20);
            $paginated = $query->paginate($perPage);

            $formatted = collect($paginated->items())->map(fn ($p) => $this->formatProduct($p));

            return response()->json([
                'success' => true,
                'data' => $formatted,
                'meta' => [
                    'current_page' => $paginated->currentPage(),
                    'last_page' => $paginated->lastPage(),
                    'per_page' => $paginated->perPage(),
                    'total' => $paginated->total(),
                ],
            ]);
        } catch (\Throwable $e) {
            Log::error('ImsIntegrationController@index failed: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch products: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get a single product by ID, slug, or variant SKU.
     */
    public function show(Request $request, $idOrSku): JsonResponse
    {
        if ($authError = $this->checkApiKey($request)) {
            return $authError;
        }

        try {
            $product = null;

            if (is_numeric($idOrSku)) {
                $product = Product::with([
                    'category',
                    'variants' => fn ($q) => $q->orderBy('sort_order'),
                    'images' => fn ($q) => $q->orderBy('sort_order'),
                ])->find($idOrSku);
            }

            if (! $product) {
                $product = Product::with([
                    'category',
                    'variants' => fn ($q) => $q->orderBy('sort_order'),
                    'images' => fn ($q) => $q->orderBy('sort_order'),
                ])->whereHas('variants', function ($vq) use ($idOrSku) {
                    $vq->where('sku', $idOrSku);
                })->first();
            }

            if (! $product) {
                $product = Product::with([
                    'category',
                    'variants' => fn ($q) => $q->orderBy('sort_order'),
                    'images' => fn ($q) => $q->orderBy('sort_order'),
                ])->where('slug', $idOrSku)->first();
            }

            if (! $product) {
                return response()->json([
                    'success' => false,
                    'message' => "Product not found with ID or SKU: {$idOrSku}",
                ], 404);
            }

            return response()->json([
                'success' => true,
                'data' => $this->formatProduct($product),
            ]);
        } catch (\Throwable $e) {
            Log::error('ImsIntegrationController@show failed: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch product: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Update product and its variants from IMS, matching by SKU.
     */
    public function update(Request $request, $idOrSku): JsonResponse
    {
        if ($authError = $this->checkApiKey($request)) {
            return $authError;
        }

        try {
            $product = null;

            if (is_numeric($idOrSku)) {
                $product = Product::with('variants')->find($idOrSku);
            }

            if (! $product) {
                $product = Product::with('variants')->whereHas('variants', function ($vq) use ($idOrSku) {
                    $vq->where('sku', $idOrSku);
                })->first();
            }

            if (! $product) {
                return response()->json([
                    'success' => false,
                    'message' => "Product not found for update with ID or SKU: {$idOrSku}",
                ], 404);
            }

            $validated = $request->validate([
                'name' => 'nullable|string|max:300',
                'description' => 'nullable|string',
                'selling_price' => 'nullable|numeric|min:0',
                'mrp' => 'nullable|numeric|min:0',
                'cost_price' => 'nullable|numeric|min:0',
                'is_active' => 'nullable|boolean',
                'variants' => 'nullable|array',
                'variants.*.sku' => 'required_with:variants|string|max:100',
                'variants.*.selling_price' => 'nullable|numeric|min:0',
                'variants.*.mrp' => 'nullable|numeric|min:0',
                'variants.*.cost_price' => 'nullable|numeric|min:0',
                'variants.*.stock_quantity' => 'nullable|integer|min:0',
                'variants.*.size' => 'nullable|string|max:30',
                'variants.*.color' => 'nullable|string|max:50',
                'variants.*.color_code' => 'nullable|string|max:10',
                'variants.*.is_active' => 'nullable|boolean',
            ]);

            DB::transaction(function () use ($product, $validated) {
                // Update parent product fields
                $updateFields = [];
                if (isset($validated['name'])) $updateFields['name'] = $validated['name'];
                if (isset($validated['description'])) $updateFields['description'] = $validated['description'];
                if (isset($validated['selling_price'])) $updateFields['selling_price'] = $validated['selling_price'];
                if (isset($validated['mrp'])) $updateFields['mrp'] = $validated['mrp'];
                if (isset($validated['cost_price'])) $updateFields['cost_price'] = $validated['cost_price'];
                if (isset($validated['is_active'])) $updateFields['is_active'] = $validated['is_active'];

                if (! empty($updateFields)) {
                    $product->update($updateFields);
                }

                // Update or create variants by SKU
                if (! empty($validated['variants'])) {
                    foreach ($validated['variants'] as $vData) {
                        $sku = strtoupper(trim($vData['sku']));
                        $variant = ProductVariant::where('product_id', $product->id)
                            ->where('sku', $sku)
                            ->first();

                        if (! $variant) {
                            // Check if variant exists elsewhere or was trashed
                            $variant = ProductVariant::withTrashed()
                                ->where('sku', $sku)
                                ->first();

                            if ($variant && $variant->trashed()) {
                                $variant->restore();
                                $variant->product_id = $product->id;
                            }
                        }

                        if ($variant) {
                            $oldStock = (int) $variant->stock_quantity;
                            $newStock = isset($vData['stock_quantity']) ? (int) $vData['stock_quantity'] : $oldStock;

                            $variantUpdate = [];
                            if (isset($vData['selling_price'])) $variantUpdate['selling_price'] = $vData['selling_price'];
                            if (isset($vData['mrp'])) $variantUpdate['mrp'] = $vData['mrp'];
                            if (isset($vData['cost_price'])) $variantUpdate['cost_price'] = $vData['cost_price'];
                            if (isset($vData['stock_quantity'])) $variantUpdate['stock_quantity'] = $newStock;
                            if (isset($vData['size'])) $variantUpdate['size'] = $vData['size'];
                            if (isset($vData['color'])) $variantUpdate['color'] = $vData['color'];
                            if (isset($vData['color_code'])) $variantUpdate['color_code'] = $vData['color_code'];
                            if (isset($vData['is_active'])) $variantUpdate['is_active'] = $vData['is_active'];

                            $variant->update($variantUpdate);

                            // Log inventory ledger if stock quantity changed
                            if ($newStock !== $oldStock) {
                                InventoryLedger::create([
                                    'product_variant_id' => $variant->id,
                                    'type' => 'ims_sync',
                                    'direction' => $newStock >= $oldStock ? 'in' : 'out',
                                    'quantity' => abs($newStock - oldStock),
                                    'unit_cost' => $variant->cost_price ?: $product->cost_price ?: 0,
                                    'stock_before' => $oldStock,
                                    'stock_after' => $newStock,
                                    'notes' => 'Synchronized from Inventory Management System',
                                ]);
                            }
                        } else {
                            // Variant doesn't exist yet, create it
                            $createdVariant = ProductVariant::create([
                                'product_id' => $product->id,
                                'sku' => $sku,
                                'size' => $vData['size'] ?? 'Free Size',
                                'color' => $vData['color'] ?? 'Standard',
                                'color_code' => $vData['color_code'] ?? null,
                                'mrp' => $vData['mrp'] ?? $product->mrp,
                                'selling_price' => $vData['selling_price'] ?? $product->selling_price,
                                'cost_price' => $vData['cost_price'] ?? $product->cost_price,
                                'stock_quantity' => $vData['stock_quantity'] ?? 0,
                                'is_active' => $vData['is_active'] ?? true,
                            ]);

                            if (! empty($vData['stock_quantity'])) {
                                InventoryLedger::create([
                                    'product_variant_id' => $createdVariant->id,
                                    'type' => 'ims_sync',
                                    'direction' => 'in',
                                    'quantity' => (int) $vData['stock_quantity'],
                                    'unit_cost' => $createdVariant->cost_price ?: $product->cost_price ?: 0,
                                    'stock_before' => 0,
                                    'stock_after' => (int) $vData['stock_quantity'],
                                    'notes' => 'Created via IMS synchronization',
                                ]);
                            }
                        }
                    }
                }
            });

            $freshProduct = Product::with(['category', 'variants', 'images'])->find($product->id);

            return response()->json([
                'success' => true,
                'message' => 'Product and variants updated successfully in e-commerce store',
                'data' => $this->formatProduct($freshProduct),
            ]);
        } catch (\Throwable $e) {
            Log::error('ImsIntegrationController@update failed: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to update product: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Create product in e-commerce from IMS.
     */
    public function store(Request $request): JsonResponse
    {
        if ($authError = $this->checkApiKey($request)) {
            return $authError;
        }

        try {
            $validated = $request->validate([
                'name' => 'required|string|max:300',
                'slug' => 'nullable|string|max:350',
                'category_name' => 'nullable|string|max:100',
                'category_id' => 'nullable|exists:categories,id',
                'description' => 'nullable|string',
                'selling_price' => 'required|numeric|min:0',
                'mrp' => 'nullable|numeric|min:0',
                'cost_price' => 'nullable|numeric|min:0',
                'is_active' => 'nullable|boolean',
                'variants' => 'required|array|min:1',
                'variants.*.sku' => 'required|string|max:100|distinct|unique:product_variants,sku',
                'variants.*.size' => 'nullable|string|max:30',
                'variants.*.color' => 'nullable|string|max:50',
                'variants.*.color_code' => 'nullable|string|max:10',
                'variants.*.selling_price' => 'nullable|numeric|min:0',
                'variants.*.mrp' => 'nullable|numeric|min:0',
                'variants.*.cost_price' => 'nullable|numeric|min:0',
                'variants.*.stock_quantity' => 'nullable|integer|min:0',
                'images' => 'nullable|array',
            ]);

            // Resolve or create category
            $categoryId = $validated['category_id'] ?? null;
            if (! $categoryId && ! empty($validated['category_name'])) {
                $category = Category::firstOrCreate(
                    ['name' => $validated['category_name']],
                    [
                        'slug' => Str::slug($validated['category_name']),
                        'is_active' => true,
                    ]
                );
                $categoryId = $category->id;
            }

            if (! $categoryId) {
                $firstCat = Category::first();
                $categoryId = $firstCat ? $firstCat->id : 1;
            }

            $mrp = $validated['mrp'] ?? $validated['selling_price'];

            $product = DB::transaction(function () use ($validated, $categoryId, $mrp) {
                $slug = ! empty($validated['slug']) ? Str::slug($validated['slug']) : Str::slug($validated['name']);
                $origSlug = $slug;
                $counter = 1;
                while (Product::where('slug', $slug)->exists()) {
                    $slug = "{$origSlug}-{$counter}";
                    $counter++;
                }

                $product = Product::create([
                    'uuid' => (string) Str::uuid(),
                    'category_id' => $categoryId,
                    'name' => $validated['name'],
                    'slug' => $slug,
                    'description' => $validated['description'] ?? null,
                    'mrp' => $mrp,
                    'selling_price' => $validated['selling_price'],
                    'cost_price' => $validated['cost_price'] ?? null,
                    'is_active' => $validated['is_active'] ?? true,
                ]);

                // Create variants with exact SKUs
                foreach ($validated['variants'] as $v) {
                    $vSku = strtoupper(trim($v['sku']));
                    $variant = ProductVariant::create([
                        'product_id' => $product->id,
                        'sku' => $vSku,
                        'size' => $v['size'] ?? 'Free Size',
                        'color' => $v['color'] ?? 'Standard',
                        'color_code' => $v['color_code'] ?? null,
                        'mrp' => $v['mrp'] ?? $mrp,
                        'selling_price' => $v['selling_price'] ?? $validated['selling_price'],
                        'cost_price' => $v['cost_price'] ?? $validated['cost_price'] ?? null,
                        'stock_quantity' => $v['stock_quantity'] ?? 0,
                        'is_active' => true,
                    ]);

                    if (! empty($v['stock_quantity'])) {
                        InventoryLedger::create([
                            'product_variant_id' => $variant->id,
                            'type' => 'ims_sync',
                            'direction' => 'in',
                            'quantity' => (int) $v['stock_quantity'],
                            'unit_cost' => $variant->cost_price ?: 0,
                            'stock_before' => 0,
                            'stock_after' => (int) $v['stock_quantity'],
                            'notes' => 'Initial creation from IMS',
                        ]);
                    }
                }

                // If images provided
                if (! empty($validated['images']) && is_array($validated['images'])) {
                    foreach ($validated['images'] as $idx => $imgUrl) {
                        ProductImage::create([
                            'product_id' => $product->id,
                            'url' => $imgUrl,
                            'image_path' => $imgUrl,
                            'is_primary' => $idx === 0,
                            'sort_order' => $idx + 1,
                        ]);
                    }
                }

                return $product;
            });

            $freshProduct = Product::with(['category', 'variants', 'images'])->find($product->id);

            return response()->json([
                'success' => true,
                'message' => 'Product created successfully in e-commerce store',
                'data' => $this->formatProduct($freshProduct),
            ], 201);
        } catch (\Throwable $e) {
            Log::error('ImsIntegrationController@store failed: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to create product in e-commerce: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Batch sync stock and price by variant SKU.
     */
    public function syncStockPrice(Request $request): JsonResponse
    {
        if ($authError = $this->checkApiKey($request)) {
            return $authError;
        }

        $validated = $request->validate([
            'items' => 'required|array|min:1',
            'items.*.sku' => 'required|string',
            'items.*.stock_quantity' => 'nullable|integer|min:0',
            'items.*.selling_price' => 'nullable|numeric|min:0',
            'items.*.cost_price' => 'nullable|numeric|min:0',
        ]);

        $updatedCount = 0;
        $notFoundSkus = [];

        DB::transaction(function () use ($validated, &$updatedCount, &$notFoundSkus) {
            foreach ($validated['items'] as $item) {
                $sku = strtoupper(trim($item['sku']));
                $variant = ProductVariant::where('sku', $sku)->first();

                if (! $variant) {
                    $notFoundSkus[] = $sku;
                    continue;
                }

                $updates = [];
                if (isset($item['selling_price'])) $updates['selling_price'] = $item['selling_price'];
                if (isset($item['cost_price'])) $updates['cost_price'] = $item['cost_price'];

                if (isset($item['stock_quantity'])) {
                    $oldStock = (int) $variant->stock_quantity;
                    $newStock = (int) $item['stock_quantity'];
                    $updates['stock_quantity'] = $newStock;

                    if ($oldStock !== $newStock) {
                        InventoryLedger::create([
                            'product_variant_id' => $variant->id,
                            'type' => 'ims_batch_sync',
                            'direction' => $newStock >= $oldStock ? 'in' : 'out',
                            'quantity' => abs($newStock - $oldStock),
                            'unit_cost' => $item['cost_price'] ?? $variant->cost_price ?: 0,
                            'stock_before' => $oldStock,
                            'stock_after' => $newStock,
                            'notes' => 'Batch stock update from IMS',
                        ]);
                    }
                }

                if (! empty($updates)) {
                    $variant->update($updates);
                    $updatedCount++;
                }
            }
        });

        return response()->json([
            'success' => true,
            'message' => "Synced {$updatedCount} items successfully",
            'updated_count' => $updatedCount,
            'not_found_skus' => $notFoundSkus,
        ]);
    }

    /**
     * Format product data with standardized fields for IMS consumption.
     */
    protected function formatProduct(Product $product): array
    {
        $variants = $product->variants ? $product->variants->where('deleted_at', null)->map(function ($v) {
            return [
                'id' => $v->id,
                'sku' => $v->sku,
                'size' => $v->size,
                'color' => $v->color,
                'color_code' => $v->color_code,
                'selling_price' => (float) ($v->selling_price ?? 0),
                'mrp' => (float) ($v->mrp ?? 0),
                'cost_price' => (float) ($v->cost_price ?? 0),
                'stock_quantity' => (int) ($v->stock_quantity ?? 0),
                'barcode' => $v->barcode,
                'is_active' => (bool) $v->is_active,
            ];
        })->values() : collect();

        $cdnBase = config('services.ims.cdn_url') ?? env('IMS_CDN_URL') ?? env('APP_URL') ?? url('/');
        $cdnBase = rtrim($cdnBase, '/');

        $toCdnUrl = function (?string $path) use ($cdnBase): ?string {
            if (! $path) {
                return null;
            }
            if (Str::startsWith($path, ['http://', 'https://', '//'])) {
                return $path;
            }
            return $cdnBase . '/' . ltrim($path, '/');
        };

        $images = $product->images ? $product->images->map(function ($img) use ($toCdnUrl) {
            return [
                'id' => $img->id,
                'url' => $toCdnUrl($img->url),
                'thumbnail_url' => $toCdnUrl($img->thumbnail_url),
                'is_primary' => (bool) $img->is_primary,
            ];
        })->values() : collect();

        // Calculate primary SKU for parent product
        $primarySku = $variants->first()['sku'] ?? "MSF-{$product->id}";

        // If all variant SKUs share a base prefix (e.g. STR-BLOUSE-BOT), extract it
        if ($variants->isNotEmpty()) {
            $firstSku = $variants->first()['sku'];
            $parts = explode('-', $firstSku);
            if (count($parts) >= 3) {
                // e.g. STR-BLOUSE-BOT-XSS-161 -> base could be STR-BLOUSE-BOT
                $prefixCandidate = implode('-', array_slice($parts, 0, count($parts) - 2));
                if (strlen($prefixCandidate) >= 3) {
                    $primarySku = $prefixCandidate;
                }
            }
        }

        $primaryImageUrl = $toCdnUrl($product->primary_image_url);
        if (! $primaryImageUrl && $images->isNotEmpty()) {
            $primaryImageUrl = $images->first()['url'];
        }

        return [
            'id' => $product->id,
            'uuid' => $product->uuid,
            'name' => $product->name,
            'slug' => $product->slug,
            'sku' => $primarySku,
            'category_id' => $product->category_id,
            'category_name' => $product->category ? $product->category->name : 'Uncategorized',
            'description' => $product->description,
            'short_description' => $product->short_description,
            'selling_price' => (float) ($product->selling_price ?? 0),
            'mrp' => (float) ($product->mrp ?? 0),
            'cost_price' => (float) ($product->cost_price ?? 0),
            'gst_rate' => (float) ($product->gst_rate ?? 0),
            'hsn_code' => $product->hsn_code,
            'total_stock' => (int) $product->total_stock,
            'is_active' => (bool) $product->is_active,
            'primary_image_url' => $primaryImageUrl,
            'images' => $images,
            'variants' => $variants,
        ];
    }
}
