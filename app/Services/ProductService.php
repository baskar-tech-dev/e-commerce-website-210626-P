<?php

namespace App\Services;

use App\Repositories\ProductRepositoryInterface;
use App\Models\Product;
use App\Models\ProductVariant;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
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
                    // Check if image is for a specific variant (by sku)
                    if (isset($imageData['variant_sku']) && isset($skuToVariantId[$imageData['variant_sku']])) {
                        $imageData['variant_id'] = $skuToVariantId[$imageData['variant_sku']];
                    }
                    $product->images()->create($imageData);
                }
            }

            return $product->load(['category', 'brand', 'variants', 'images', 'tags']);
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
                    if (isset($imageData['variant_sku']) && isset($skuToVariantId[$imageData['variant_sku']])) {
                        $imageData['variant_id'] = $skuToVariantId[$imageData['variant_sku']];
                    }

                    if (isset($imageData['id'])) {
                        $image = $product->images()->find($imageData['id']);
                        if ($image) {
                            $image->update($imageData);
                            $incomingImageIds[] = $image->id;
                        }
                    } else {
                        $newImage = $product->images()->create($imageData);
                        $incomingImageIds[] = $newImage->id;
                    }
                }
                $product->images()->whereNotIn('id', $incomingImageIds)->delete();
            }

            return $product->load(['category', 'brand', 'variants', 'images', 'tags']);
        });
    }

    /**
     * Delete product.
     */
    public function deleteProduct(int $id): bool
    {
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
