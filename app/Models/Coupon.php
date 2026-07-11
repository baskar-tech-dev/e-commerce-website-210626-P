<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Coupon extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'code',
        'name',
        'description',
        'type',
        'value',
        'max_discount',
        'min_order_value',
        'max_uses_total',
        'max_uses_per_user',
        'times_used',
        'applicable_category_ids',
        'applicable_brand_ids',
        'applicable_product_ids',
        'excluded_product_ids',
        'first_order_only',
        'is_auto_apply',
        'is_combinable',
        'is_active',
        'starts_at',
        'expires_at',
        'created_by',
    ];

    protected $casts = [
        'value' => 'decimal:2',
        'max_discount' => 'decimal:2',
        'min_order_value' => 'decimal:2',
        'max_uses_total' => 'integer',
        'max_uses_per_user' => 'integer',
        'times_used' => 'integer',
        'applicable_category_ids' => 'json',
        'applicable_brand_ids' => 'json',
        'applicable_product_ids' => 'json',
        'excluded_product_ids' => 'json',
        'first_order_only' => 'boolean',
        'is_auto_apply' => 'boolean',
        'is_combinable' => 'boolean',
        'is_active' => 'boolean',
        'starts_at' => 'datetime',
        'expires_at' => 'datetime',
    ];

    /**
     * Boot model to force uppercase codes.
     */
    protected static function boot()
    {
        parent::boot();

        static::saving(function ($coupon) {
            if (!empty($coupon->code)) {
                $coupon->code = strtoupper($coupon->code);
            }
        });
    }

    /**
     * Get user who created the coupon.
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Get usages.
     */
    public function usages(): HasMany
    {
        return $this->hasMany(CouponUsage::class);
    }
}
