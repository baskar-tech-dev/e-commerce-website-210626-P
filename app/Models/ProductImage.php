<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProductImage extends Model
{
    use HasFactory;

    protected $fillable = [
        'product_id',
        'variant_id',
        'color_group',
        'url',
        'thumbnail_url',
        'alt_text',
        'sort_order',
        'is_primary',
    ];

    protected $casts = [
        'is_primary' => 'boolean',
        'sort_order' => 'integer',
    ];

    protected $appends = ['image_path'];

    /**
     * Get the image_path attribute alias for frontend compatibility.
     */
    public function getImagePathAttribute(): ?string
    {
        return $this->url;
    }

    /**
     * Get the metadata for the image.
     */
    public function metadata(): \Illuminate\Database\Eloquent\Relations\HasOne
    {
        return $this->hasOne(ImageMetadata::class, 'product_image_id');
    }

    /**
     * Get the product that owns this image.
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    /**
     * Get the variant that owns this image.
     */
    public function variant(): BelongsTo
    {
        return $this->belongsTo(ProductVariant::class, 'variant_id');
    }
}
