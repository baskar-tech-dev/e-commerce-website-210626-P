<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

use App\Models\Traits\Auditable;

class Order extends Model
{
    use HasFactory, SoftDeletes, Auditable;

    protected $fillable = [
        'uuid',
        'order_number',
        'user_id',
        'status',
        'payment_status',
        'payment_method',
        'payment_gateway',
        'razorpay_order_id',
        'razorpay_payment_id',
        'razorpay_signature',
        'payment_response',
        'paid_at',
        'subtotal',
        'discount_amount',
        'tax_amount',
        'cgst_amount',
        'sgst_amount',
        'igst_amount',
        'shipping_amount',
        'grand_total',
        'total_items',
        'currency',
        
        // Shipping Address
        'shipping_first_name',
        'shipping_last_name',
        'shipping_phone',
        'shipping_address_line_1',
        'shipping_address_line_2',
        'shipping_city',
        'shipping_state',
        'shipping_postal_code',
        'shipping_country',
        
        // Billing Address
        'billing_first_name',
        'billing_last_name',
        'billing_phone',
        'billing_address_line_1',
        'billing_address_line_2',
        'billing_city',
        'billing_state',
        'billing_postal_code',
        'billing_country',
        
        // Tracking
        'shipping_method',
        'tracking_number',
        'courier_name',
        'estimated_delivery_at',
        'shipped_at',
        'delivered_at',
        'cancelled_at',
        'cancellation_reason',
        
        'notes',
        'admin_notes',
        'ip_address',
        'user_agent',
    ];

    protected $casts = [
        'subtotal' => 'decimal:2',
        'discount_amount' => 'decimal:2',
        'tax_amount' => 'decimal:2',
        'cgst_amount' => 'decimal:2',
        'sgst_amount' => 'decimal:2',
        'igst_amount' => 'decimal:2',
        'shipping_amount' => 'decimal:2',
        'grand_total' => 'decimal:2',
        'total_items' => 'integer',
        'estimated_delivery_at' => 'datetime',
        'shipped_at' => 'datetime',
        'delivered_at' => 'datetime',
        'cancelled_at' => 'datetime',
        'payment_response' => 'json',
        'paid_at' => 'datetime',
    ];

    /**
     * Auto generate UUID and Order number prefix.
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($order) {
            if (empty($order->uuid)) {
                $order->uuid = (string) Str::uuid();
            }
            if (empty($order->order_number)) {
                $datePrefix = 'ORD-' . date('Ymd') . '-';
                $lastOrder = static::where('order_number', 'like', $datePrefix . '%')
                    ->orderBy('id', 'desc')
                    ->first();
                $nextSequence = 1;
                if ($lastOrder) {
                    $parts = explode('-', $lastOrder->order_number);
                    $lastSequence = (int) end($parts);
                    $nextSequence = $lastSequence + 1;
                }
                $order->order_number = $datePrefix . str_pad($nextSequence, 5, '0', STR_PAD_LEFT);
            }
        });
    }

    /**
     * Get the user who placed this order.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the items in this order.
     */
    public function items(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    /**
     * Get status logs.
     */
    public function statusHistory(): HasMany
    {
        return $this->hasMany(OrderStatusHistory::class)->orderBy('created_at', 'desc');
    }

    /**
     * Get the payments for this order.
     */
    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }

    /**
     * Get the return requests for this order.
     */
    public function returns(): HasMany
    {
        return $this->hasMany(OrderReturn::class);
    }

    /**
     * Get the refunds for this order.
     */
    public function refunds(): HasMany
    {
        return $this->hasMany(Refund::class);
    }
}
