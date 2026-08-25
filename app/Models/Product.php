<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

use App\Models\Traits\Auditable;

class Product extends Model
{
    use HasFactory, SoftDeletes, Auditable;

    protected $fillable = [
        'uuid',
        'category_id',
        'name',
        'slug',
        'short_description',
        'description',
        'material',
        'care_instructions',
        'mrp',
        'selling_price',
        'cost_price',
        'tax_category',
        'gst_rate',
        'hsn_code',
        'weight',
        'is_active',
        'is_featured',
        'is_new_arrival',
        'is_bestseller',
        'badge',
        'occasion',
        'is_returnable',
        'return_window_days',
        'reviews_enabled',
        'avg_rating',
        'total_reviews',
        'total_sold',
        'meta_title',
        'meta_description',
        'meta_keywords',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'is_featured' => 'boolean',
        'is_new_arrival' => 'boolean',
        'is_bestseller' => 'boolean',
        'is_returnable' => 'boolean',
        'reviews_enabled' => 'boolean',
        'return_window_days' => 'integer',
        'avg_rating' => 'decimal:2',
        'total_reviews' => 'integer',
        'total_sold' => 'integer',
        'mrp' => 'decimal:2',
        'selling_price' => 'decimal:2',
        'cost_price' => 'decimal:2',
        'gst_rate' => 'decimal:2',
        'weight' => 'decimal:2',
    ];

    protected $appends = [
        'min_price',
        'max_price',
        'min_mrp',
        'max_mrp',
        'has_price_range',
        'price_display',
        'mrp_display',
        'primary_image_url',
        'total_stock',
        'stock_quantity',
        'is_sold_out',
        'is_low_stock',
    ];

    /**
     * Boot function for auto-generating product UUID.
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($product) {
            if (empty($product->uuid)) {
                $product->uuid = (string) Str::uuid();
            }
        });
    }

    /**
     * Get the category that owns the product.
     */
    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }


    /**
     * Get the variants for the product.
     */
    public function variants(): HasMany
    {
        return $this->hasMany(ProductVariant::class)->orderBy('sort_order');
    }

    /**
     * Get the images for the product.
     */
    public function images(): HasMany
    {
        return $this->hasMany(ProductImage::class)->orderBy('sort_order');
    }

    /**
     * Get the tags associated with the product.
     */
    public function tags(): BelongsToMany
    {
        return $this->belongsToMany(Tag::class);
    }

    /**
     * All reviews for this product.
     */
    public function reviews(): HasMany
    {
        return $this->hasMany(ProductReview::class);
    }

    /**
     * Approved reviews for public display.
     */
    public function approvedReviews(): HasMany
    {
        return $this->hasMany(ProductReview::class)->where('status', 'approved');
    }

    /**
     * Recalculate average rating and total approved review count.
     */
    public function recalculateRating(): void
    {
        $approvedCount = $this->approvedReviews()->count();
        $avgRating = $approvedCount > 0 ? (float) $this->approvedReviews()->avg('rating') : 0.00;

        $this->avg_rating = round($avgRating, 2);
        $this->total_reviews = $approvedCount;
        $this->save();
    }

    /**
     * Get minimum selling price across variants or base product.
     */
    public function getMinPriceAttribute(): float
    {
        if ($this->relationLoaded('variants') && $this->variants->isNotEmpty()) {
            $prices = $this->variants->pluck('selling_price')->filter()->map(fn($p) => (float)$p);
            if ($prices->isNotEmpty()) {
                return round($prices->min(), 2);
            }
        }
        return (float) ($this->selling_price ?? 0);
    }

    /**
     * Get maximum selling price across variants or base product.
     */
    public function getMaxPriceAttribute(): float
    {
        if ($this->relationLoaded('variants') && $this->variants->isNotEmpty()) {
            $prices = $this->variants->pluck('selling_price')->filter()->map(fn($p) => (float)$p);
            if ($prices->isNotEmpty()) {
                return round($prices->max(), 2);
            }
        }
        return (float) ($this->selling_price ?? 0);
    }

    /**
     * Get minimum MRP across variants or base product.
     */
    public function getMinMrpAttribute(): float
    {
        if ($this->relationLoaded('variants') && $this->variants->isNotEmpty()) {
            $mrps = $this->variants->pluck('mrp')->filter()->map(fn($p) => (float)$p);
            if ($mrps->isNotEmpty()) {
                return round($mrps->min(), 2);
            }
        }
        return (float) ($this->mrp ?? 0);
    }

    /**
     * Get maximum MRP across variants or base product.
     */
    public function getMaxMrpAttribute(): float
    {
        if ($this->relationLoaded('variants') && $this->variants->isNotEmpty()) {
            $mrps = $this->variants->pluck('mrp')->filter()->map(fn($p) => (float)$p);
            if ($mrps->isNotEmpty()) {
                return round($mrps->max(), 2);
            }
        }
        return (float) ($this->mrp ?? 0);
    }

    /**
     * Check if product prices vary across sizes / variants.
     */
    public function getHasPriceRangeAttribute(): bool
    {
        return $this->min_price < $this->max_price;
    }

    /**
     * Helper to format price cleanly without trailing .00
     */
    protected function formatPriceValue(float $amount): string
    {
        if (floor($amount) == $amount) {
            return number_format($amount, 0);
        }
        return number_format($amount, 2);
    }

    /**
     * Formatted selling price range (e.g. ₹599 - ₹799 or ₹599).
     */
    public function getPriceDisplayAttribute(): string
    {
        if ($this->has_price_range) {
            $min = $this->formatPriceValue($this->min_price);
            $max = $this->formatPriceValue($this->max_price);
            return "₹{$min} - ₹{$max}";
        }
        $val = $this->formatPriceValue($this->min_price ?: (float)($this->selling_price ?? 0));
        return "₹{$val}";
    }

    /**
     * Formatted MRP price display.
     */
    public function getMrpDisplayAttribute(): ?string
    {
        if ($this->min_mrp > $this->min_price || $this->max_mrp > $this->max_price) {
            if ($this->min_mrp < $this->max_mrp) {
                $min = $this->formatPriceValue($this->min_mrp);
                $max = $this->formatPriceValue($this->max_mrp);
                return "₹{$min} - ₹{$max}";
            }
            $val = $this->formatPriceValue($this->max_mrp ?: (float)($this->mrp ?? 0));
            return "₹{$val}";
        }
        return null;
    }

    /**
     * Primary cover image URL.
     */
    public function getPrimaryImageUrlAttribute(): ?string
    {
        if ($this->relationLoaded('images')) {
            if ($this->images && $this->images->isNotEmpty()) {
                $primary = $this->images->firstWhere('is_primary', true) ?? $this->images->first();
                return $primary?->url ?? $primary?->image_path;
            }
            return null;
        }

        $primary = $this->images()->where('is_primary', true)->first() ?? $this->images()->first();
        return $primary?->url ?? $primary?->image_path;
    }

    /**
     * Total available stock across all variants.
     */
    public function getTotalStockAttribute(): int
    {
        if ($this->relationLoaded('variants')) {
            return (int) $this->variants->where('is_active', true)->sum('stock_quantity');
        }
        return (int) $this->variants()->where('is_active', true)->sum('stock_quantity');
    }

    /**
     * Alias for stock_quantity.
     */
    public function getStockQuantityAttribute(): int
    {
        return $this->total_stock;
    }

    /**
     * Check if product is completely sold out.
     */
    public function getIsSoldOutAttribute(): bool
    {
        return $this->total_stock <= 0;
    }

    /**
     * Check if product has low stock (urgency indicator: 1-5 items left).
     */
    public function getIsLowStockAttribute(): bool
    {
        $stock = $this->total_stock;
        return $stock > 0 && $stock <= 5;
    }
}
