<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Str;

class OrderReturn extends Model
{
    use HasFactory;

    protected $table = 'returns';

    protected $fillable = [
        'uuid',
        'return_number',
        'order_id',
        'user_id',
        'status',
        'reason',
        'description',
        'admin_notes',
        'refund_amount',
        'pickup_scheduled_at',
        'picked_up_at',
        'qc_completed_at',
        'approved_at',
        'rejected_at',
        'rejection_reason',
    ];

    protected $casts = [
        'refund_amount' => 'decimal:2',
        'pickup_scheduled_at' => 'datetime',
        'picked_up_at' => 'datetime',
        'qc_completed_at' => 'datetime',
        'approved_at' => 'datetime',
        'rejected_at' => 'datetime',
    ];

    /**
     * Boot logic for automatically assigning UUID and sequential return_number.
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            if (empty($model->uuid)) {
                $model->uuid = (string) Str::uuid();
            }

            if (empty($model->return_number)) {
                $datePrefix = 'RET-' . date('Ymd') . '-';
                
                // Count how many returns were created today
                $todayCount = self::where('return_number', 'like', $datePrefix . '%')->count();
                $sequence = str_pad($todayCount + 1, 5, '0', STR_PAD_LEFT);
                
                $model->return_number = $datePrefix . $sequence;
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
     * Get the user.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get return items.
     */
    public function items(): HasMany
    {
        return $this->hasMany(ReturnItem::class, 'return_id');
    }

    /**
     * Get refund.
     */
    public function refund(): HasOne
    {
        return $this->hasOne(Refund::class, 'return_id');
    }
}
