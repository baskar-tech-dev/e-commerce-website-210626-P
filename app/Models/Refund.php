<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class Refund extends Model
{
    use HasFactory;

    protected $fillable = [
        'uuid',
        'payment_id',
        'order_id',
        'return_id',
        'gateway_refund_id',
        'amount',
        'reason',
        'status',
        'processed_at',
        'gateway_response',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'processed_at' => 'datetime',
        'gateway_response' => 'json',
    ];

    /**
     * Boot function to assign UUID automatically.
     */
    protected static function boot()
    {
        parent::boot();
        static::creating(function ($model) {
            if (empty($model->uuid)) {
                $model->uuid = (string) Str::uuid();
            }
        });
    }

    /**
     * Get original payment.
     */
    public function payment(): BelongsTo
    {
        return $this->belongsTo(Payment::class);
    }

    /**
     * Get order.
     */
    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    /**
     * Get return request.
     */
    public function returnRequest(): BelongsTo
    {
        return $this->belongsTo(OrderReturn::class, 'return_id');
    }
}
