<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ReturnItem extends Model
{
    use HasFactory;

    protected $table = 'return_items';

    protected $fillable = [
        'return_id',
        'order_item_id',
        'product_variant_id',
        'quantity',
        'reason',
        'photos',
        'qc_status',
        'qc_notes',
    ];

    protected $casts = [
        'quantity' => 'integer',
        'photos' => 'json',
    ];

    /**
     * Get the parent return request.
     */
    public function returnRequest(): BelongsTo
    {
        return $this->belongsTo(OrderReturn::class, 'return_id');
    }

    /**
     * Get the order item.
     */
    public function orderItem(): BelongsTo
    {
        return $this->belongsTo(OrderItem::class);
    }

    /**
     * Get the product variant.
     */
    public function variant(): BelongsTo
    {
        return $this->belongsTo(ProductVariant::class, 'product_variant_id');
    }
}
