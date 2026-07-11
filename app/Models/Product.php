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
        'is_returnable',
        'return_window_days',
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
}
