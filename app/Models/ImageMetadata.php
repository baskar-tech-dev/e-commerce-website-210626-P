<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ImageMetadata extends Model
{
    use HasFactory;

    protected $table = 'image_metadata';

    protected $fillable = [
        'product_image_id',
        'width',
        'height',
        'file_size',
        'format',
        'color_space',
        'dpi',
        'compression_ratio',
        'hash',
        'uploaded_by',
    ];

    public function productImage(): BelongsTo
    {
        return $this->belongsTo(ProductImage::class, 'product_image_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }
}
