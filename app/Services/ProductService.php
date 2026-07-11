<?php

namespace App\Services;

use App\Repositories\ProductRepositoryInterface;
use App\Models\Product;
use App\Models\ProductVariant;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class ProductService
{
    protected $productRepository;

    public function __construct(ProductRepositoryInterface $productRepository)
    {
        $this->productRepository = $productRepository;
    }

    /**
     * Get paginated products list with filters.
     */
    public function getPaginatedProducts(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        return $this->productRepository->all($filters, $perPage);
    }

    /**
     * Find product by ID.
     */
    public function getProductById(int $id): ?Product
    {
        return $this->productRepository->find($id);
    }

    /**
     * Find product by slug.
     */
    public function getProductBySlug(string $slug): ?Product
    {
        return $this->productRepository->findBySlug($slug);
    }

    /**
     * Create product with variants, images, and tags.
     */
    public function createProduct(array $data): Product
    {
        return DB::transaction(function () use ($data) {
            if (empty($data['slug'])) {
                $data['slug'] = Str::slug($data['name']);
            } else {
                $data['slug'] = Str::slug($data['slug']);
            }
            $data['slug'] = $this->makeSlugUnique($data['slug']);

            // Create product
            $product = $this->productRepository->create($data);

            // Sync tags
            if (isset($data['tag_ids'])) {
                $product->tags()->sync($data['tag_ids']);
            }

            // Track variants created to match images to variant ids
            $skuToVariantId = [];

            // Create variants
            if (isset($data['variants']) && is_array($data['variants'])) {
                foreach ($data['variants'] as $variantData) {
                    $this->validateSKU($variantData['sku']);
                    $variant = $product->variants()->create($variantData);
                    $skuToVariantId[$variant->sku] = $variant->id;
                }
            }

            // Create images
            if (isset($data['images']) && is_array($data['images'])) {
                foreach ($data['images'] as $imageData) {
                    $variantId = null;
                    if (isset($imageData['variant_sku']) && isset($skuToVariantId[$imageData['variant_sku']])) {
                        $variantId = $skuToVariantId[$imageData['variant_sku']];
                    }
                    $this->processUploadedTempImage($product, $imageData, $variantId);
                }
            }

            return $product->load(['category', 'variants', 'images', 'tags']);
        });
    }

    /**
     * Update product with variants, images, and tags.
     */
    public function updateProduct(int $id, array $data): ?Product
    {
        return DB::transaction(function () use ($id, $data) {
            $product = $this->productRepository->find($id);
            if (!$product) {
                return null;
            }

            if (!empty($data['name']) && empty($data['slug'])) {
                $data['slug'] = Str::slug($data['name']);
            } elseif (!empty($data['slug'])) {
                $data['slug'] = Str::slug($data['slug']);
            }

            if (!empty($data['slug'])) {
                $data['slug'] = $this->makeSlugUnique($data['slug'], $id);
            }

            // Update product
            $product->update($data);

            // Sync tags
            if (isset($data['tag_ids'])) {
                $product->tags()->sync($data['tag_ids']);
            }

            // Track SKUs to IDs
            $skuToVariantId = [];

            // Sync variants
            if (isset($data['variants']) && is_array($data['variants'])) {
                $incomingVariantIds = [];
                foreach ($data['variants'] as $variantData) {
                    $variantId = $variantData['id'] ?? null;
                    $this->validateSKU($variantData['sku'], $variantId);

                    if ($variantId) {
                        $variant = $product->variants()->find($variantId);
                        if ($variant) {
                            $variant->update($variantData);
                            $incomingVariantIds[] = $variant->id;
                            $skuToVariantId[$variant->sku] = $variant->id;
                        }
                    } else {
                        $newVariant = $product->variants()->create($variantData);
                        $incomingVariantIds[] = $newVariant->id;
                        $skuToVariantId[$newVariant->sku] = $newVariant->id;
                    }
                }
                // Delete variants that were not in the request
                $product->variants()->whereNotIn('id', $incomingVariantIds)->delete();
            }

            // Sync images
            if (isset($data['images']) && is_array($data['images'])) {
                $incomingImageIds = [];
                foreach ($data['images'] as $imageData) {
                    // Resolve variant_id from sku if passed
                    $variantId = null;
                    if (isset($imageData['variant_sku']) && isset($skuToVariantId[$imageData['variant_sku']])) {
                        $variantId = $skuToVariantId[$imageData['variant_sku']];
                    }

                    if (isset($imageData['id'])) {
                        $image = $product->images()->find($imageData['id']);
                        if ($image) {
                            $image->update([
                                'variant_id' => $variantId,
                                'color_group' => $imageData['color_group'] ?? $image->color_group,
                                'url' => $imageData['url'],
                                'thumbnail_url' => $imageData['thumbnail_url'] ?? $image->thumbnail_url,
                                'alt_text' => $imageData['alt_text'] ?? $image->alt_text,
                                'sort_order' => $imageData['sort_order'] ?? $image->sort_order,
                                'is_primary' => $imageData['is_primary'] ?? $image->is_primary,
                            ]);
                            $incomingImageIds[] = $image->id;
                        }
                    } else {
                        $newImage = $this->processUploadedTempImage($product, $imageData, $variantId);
                        $incomingImageIds[] = $newImage->id;
                    }
                }
                // Delete physical files of images that are removed
                $removedImages = $product->images()->whereNotIn('id', $incomingImageIds)->get();
                foreach ($removedImages as $removedImg) {
                    $this->deletePhysicalFiles($removedImg->url);
                    $this->deletePhysicalFiles($removedImg->thumbnail_url);
                    $removedImg->delete();
                }
            }

            return $product->load(['category', 'variants', 'images', 'tags']);
        });
    }

    /**
     * Process temp uploaded images.
     */
    protected function processUploadedTempImage($product, array $imageData, ?int $variantId = null)
    {
        if (isset($imageData['temp_path']) && is_array($imageData['temp_path'])) {
            $temp = $imageData['temp_path'];

            // Move original
            $origName = basename($temp['original']);
            $newOrig = "products/{$product->id}/original/{$origName}";
            if (Storage::disk('public')->exists($temp['original'])) {
                Storage::disk('public')->move($temp['original'], $newOrig);
            }

            // Move webp
            $webpName = basename($temp['webp']);
            $newWebp = "products/{$product->id}/webp/{$webpName}";
            if (Storage::disk('public')->exists($temp['webp'])) {
                Storage::disk('public')->move($temp['webp'], $newWebp);
            }

            // Move thumbnail
            $thumbName = basename($temp['thumb']);
            $newThumb = "products/{$product->id}/thumbnails/{$thumbName}";
            if (Storage::disk('public')->exists($temp['thumb'])) {
                Storage::disk('public')->move($temp['thumb'], $newThumb);
            }

            $url = '/storage/' . $newWebp;
            $thumbnailUrl = '/storage/' . $newThumb;

            $imgRecord = $product->images()->create([
                'variant_id' => $variantId,
                'color_group' => $imageData['color_group'] ?? null,
                'url' => $url,
                'thumbnail_url' => $thumbnailUrl,
                'alt_text' => $imageData['alt_text'] ?? 'Product image',
                'sort_order' => $imageData['sort_order'] ?? 0,
                'is_primary' => $imageData['is_primary'] ?? false,
            ]);

            // Create metadata
            \App\Models\ImageMetadata::create([
                'product_image_id' => $imgRecord->id,
                'width' => $temp['width'] ?? 0,
                'height' => $temp['height'] ?? 0,
                'file_size' => $temp['file_size'] ?? 0,
                'format' => 'webp',
                'hash' => $temp['hash'] ?? '',
                'uploaded_by' => auth()->user()?->id,
            ]);

            return $imgRecord;
        }

        // Otherwise it is a simple image record
        $imgRecord = $product->images()->create([
            'variant_id' => $variantId,
            'color_group' => $imageData['color_group'] ?? null,
            'url' => $imageData['url'],
            'thumbnail_url' => $imageData['thumbnail_url'] ?? null,
            'alt_text' => $imageData['alt_text'] ?? null,
            'sort_order' => $imageData['sort_order'] ?? 0,
            'is_primary' => $imageData['is_primary'] ?? false,
        ]);

        return $imgRecord;
    }

    /**
     * Delete files from local storage by URL.
     */
    protected function deletePhysicalFiles(?string $url): void
    {
        if (empty($url)) return;
        $path = preg_replace('/^\/?storage\//', '', parse_url($url, PHP_URL_PATH));
        if (Storage::disk('public')->exists($path)) {
            Storage::disk('public')->delete($path);
        }
    }

    public function deleteProduct(int $id): bool
    {
        // Check if used in sales orders
        $isUsedInOrders = \App\Models\OrderItem::where('product_id', $id)->exists();
        
        $variantIds = \App\Models\ProductVariant::where('product_id', $id)->pluck('id');
        $isVariantUsedInOrders = \App\Models\OrderItem::whereIn('product_variant_id', $variantIds)->exists();
        $isVariantUsedInPurchase = \App\Models\PurchaseOrderItem::whereIn('product_variant_id', $variantIds)->exists();
        
        if ($isUsedInOrders || $isVariantUsedInOrders || $isVariantUsedInPurchase) {
            throw new \Exception("This product has been used in orders or purchase logs. Would you like to deactivate it instead?", 409);
        }

        return DB::transaction(function () use ($id) {
            $product = $this->productRepository->find($id);
            if ($product) {
                // Soft delete associated variants
                $product->variants()->delete();
                // Hard delete images since they do not use SoftDeletes
                $product->images()->delete();
                // Soft delete parent product
                return $this->productRepository->delete($id);
            }
            return false;
        });
    }

    /**
     * Ensure variant SKU is unique across all products.
     */
    protected function validateSKU(string $sku, ?int $excludeVariantId = null): void
    {
        $query = ProductVariant::where('sku', $sku);
        if ($excludeVariantId !== null) {
            $query->where('id', '!=', $excludeVariantId);
        }

        if ($query->exists()) {
            throw ValidationException::withMessages([
                'variants' => ["The SKU '{$sku}' is already taken."]
            ]);
        }
    }

    /**
     * Make slug unique.
     */
    protected function makeSlugUnique(string $slug, ?int $excludeProductId = null): string
    {
        $originalSlug = $slug;
        $count = 1;

        while (true) {
            $query = Product::where('slug', $slug);
            if ($excludeProductId !== null) {
                $query->where('id', '!=', $excludeProductId);
            }

            if (!$query->exists()) {
                break;
            }

            $slug = $originalSlug . '-' . $count;
            $count++;
        }

        return $slug;
    }
}
