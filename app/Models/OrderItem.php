<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OrderItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_id',
        'product_id',
        'product_variant_id',
        'product_name',
        'variant_name',
        'sku',
        'quantity',
        'unit_price',
        'unit_mrp',
        'discount',
        'tax_amount',
        'tax_rate',
        'total_price',
        'status',
    ];

    protected $casts = [
        'quantity' => 'integer',
        'unit_price' => 'decimal:2',
        'unit_mrp' => 'decimal:2',
        'discount' => 'decimal:2',
        'tax_amount' => 'decimal:2',
        'tax_rate' => 'decimal:2',
        'total_price' => 'decimal:2',
    ];

    protected $appends = [
        'image_url',
        'product_slug',
        'product_uuid',
    ];

    /**
     * Resolve product item thumbnail image URL.
     */
    public function getImageUrlAttribute(): ?string
    {
        // 1. Variant specific images
        if ($this->relationLoaded('variant') && $this->variant) {
            if ($this->variant->relationLoaded('images') && $this->variant->images->isNotEmpty()) {
                return $this->variant->images->first()?->url;
            }
            if ($this->variant->product) {
                return $this->variant->product->primary_image_url;
            }
        }

        // 2. Direct loaded product
        if ($this->relationLoaded('product') && $this->product) {
            return $this->product->primary_image_url;
        }

        // 3. Query via product_id
        if ($this->product_id) {
            $image = ProductImage::where('product_id', $this->product_id)
                ->orderByDesc('is_primary')
                ->orderBy('sort_order')
                ->first();
            if ($image) {
                return $image->url ?? $image->image_path;
            }
        }

        // 4. Query via SKU
        if ($this->sku) {
            $variant = ProductVariant::where('sku', $this->sku)->first();
            if ($variant) {
                $image = ProductImage::where('product_id', $variant->product_id)
                    ->orderByDesc('is_primary')
                    ->orderBy('sort_order')
                    ->first();
                if ($image) {
                    return $image->url ?? $image->image_path;
                }
            }
        }

        // 5. Query via product_name
        if ($this->product_name) {
            $cleanName = trim(preg_replace('/[^\w\s]/u', '', $this->product_name));
            $product = Product::where('name', $this->product_name)
                ->orWhere('name', 'like', "%{$cleanName}%")
                ->first();
            if ($product) {
                return $product->primary_image_url;
            }
        }

        return null;
    }

    /**
     * Resolve product slug or uuid for storefront navigation.
     */
    public function getProductSlugAttribute(): ?string
    {
        if ($this->relationLoaded('product') && $this->product) {
            return $this->product->uuid ?? $this->product->slug ?? (string)$this->product->id;
        }
        if ($this->relationLoaded('variant') && $this->variant?->product) {
            return $this->variant->product->uuid ?? $this->variant->product->slug ?? (string)$this->variant->product->id;
        }
        if ($this->product_id) {
            $prod = Product::find($this->product_id);
            if ($prod) return $prod->uuid ?? $prod->slug ?? (string)$prod->id;
        }
        if ($this->sku) {
            $variant = ProductVariant::where('sku', $this->sku)->with('product')->first();
            if ($variant && $variant->product) {
                return $variant->product->uuid ?? $variant->product->slug ?? (string)$variant->product->id;
            }
        }
        if ($this->product_name) {
            $prod = Product::where('name', $this->product_name)->first();
            if ($prod) return $prod->uuid ?? $prod->slug ?? (string)$prod->id;
        }
        return null;
    }

    /**
     * Resolve product UUID.
     */
    public function getProductUuidAttribute(): ?string
    {
        if ($this->relationLoaded('product') && $this->product) {
            return $this->product->uuid;
        }
        if ($this->relationLoaded('variant') && $this->variant?->product) {
            return $this->variant->product->uuid;
        }
        if ($this->product_id) {
            $prod = Product::find($this->product_id);
            if ($prod) return $prod->uuid;
        }
        if ($this->sku) {
            $variant = ProductVariant::where('sku', $this->sku)->with('product')->first();
            if ($variant && $variant->product) return $variant->product->uuid;
        }
        if ($this->product_name) {
            $prod = Product::where('name', $this->product_name)->first();
            if ($prod) return $prod->uuid;
        }
        return null;
    }

    /**
     * Get the order associated with this item.
     */
    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    /**
     * Get the product.
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    /**
     * Get the variant.
     */
    public function variant(): BelongsTo
    {
        return $this->belongsTo(ProductVariant::class, 'product_variant_id');
    }
}
