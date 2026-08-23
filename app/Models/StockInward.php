<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class StockInward extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'inward_number',
        'inward_date',
        'category_id',
        'factory_id',
        'supplier_name',
        'reference_no',
        'status',
        'total_items',
        'total_quantity',
        'total_amount',
        'notes',
        'created_by',
    ];

    protected $casts = [
        'inward_date' => 'date:Y-m-d',
        'total_items' => 'integer',
        'total_quantity' => 'integer',
        'total_amount' => 'decimal:2',
    ];

    /**
     * Optional primary category associated with this inward shipment.
     */
    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class, 'category_id');
    }

    /**
     * Factory / Manufacturing unit where stock was produced.
     */
    public function factory(): BelongsTo
    {
        return $this->belongsTo(Factory::class, 'factory_id');
    }

    /**
     * Line items in this inward shipment.
     */
    public function items(): HasMany
    {
        return $this->hasMany(StockInwardItem::class, 'stock_inward_id');
    }

    /**
     * Staff member who recorded this inward.
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
