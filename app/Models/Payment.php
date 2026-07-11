<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;
use App\Models\Traits\Auditable;

class Payment extends Model
{
    use HasFactory, Auditable;

    protected $fillable = [
        'uuid',
        'order_id',
        'gateway',
        'gateway_payment_id',
        'gateway_order_id',
        'gateway_signature',
        'method',
        'amount',
        'currency',
        'status',
        'failure_reason',
        'paid_at',
        'gateway_response',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'paid_at' => 'datetime',
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
     * Get the order.
     */
    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    /**
     * Get associated refunds.
     */
    public function refunds(): HasMany
    {
        return $this->hasMany(Refund::class);
    }
}
