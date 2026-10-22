<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StockInwardItem extends Model
{
    use HasFactory;

    protected $primaryKey = 'stock_inward_item_id';
    protected $appends = ['id'];

    public function getKeyName()
    {
        return \Illuminate\Support\Facades\Schema::hasColumn($this->getTable(), 'stock_inward_item_id') 
            ? 'stock_inward_item_id' 
            : 'id';
    }

    public function getIdAttribute(): ?int
    {
        return $this->attributes['stock_inward_item_id'] ?? $this->attributes['id'] ?? null;
    }

    protected $fillable = [
        'stock_inward_id',
        'product_id',
        'product_variant_id',
        'color',
        'color_id',
        'size',
        'sku',
        'quantity',
        'unit_cost',
        'subtotal',
    ];

    protected $casts = [
        'quantity' => 'integer',
        'unit_cost' => 'decimal:2',
        'subtotal' => 'decimal:2',
    ];

    /**
     * Parent inward record.
     */
    public function inward(): BelongsTo
    {
        return $this->belongsTo(StockInward::class, 'stock_inward_id');
    }

    /**
     * Associated product.
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class, 'product_id');
    }

    /**
     * Associated variant.
     */
    public function variant(): BelongsTo
    {
        return $this->belongsTo(ProductVariant::class, 'product_variant_id');
    }

    /**
     * Associated color master.
     */
    public function colorMaster(): BelongsTo
    {
        return $this->belongsTo(Color::class, 'color_id');
    }
}
