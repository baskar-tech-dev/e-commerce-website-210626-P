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

    // 9 Standardized Order Statuses
    public const STATUS_ORDER_PLACED    = 'order_placed';    // 1. Customer successfully placed the order
    public const STATUS_ORDER_CONFIRMED = 'order_confirmed'; // 2. Admin accepted/confirmed the order
    public const STATUS_PROCESSING      = 'processing';      // 3. Order is being prepared
    public const STATUS_READY_TO_SHIP   = 'ready_to_ship';   // 4. Product is packed and ready
    public const STATUS_SHIPPED         = 'shipped';         // 5. Order handed over to courier
    public const STATUS_DELIVERED       = 'delivered';       // 6. Customer received the order
    public const STATUS_CANCELLED       = 'cancelled';       // 7. Order was cancelled
    public const STATUS_RETURNED        = 'returned';        // 8. Product was returned
    public const STATUS_REFUNDED        = 'refunded';        // 9. Refund completed

    public const STATUSES = [
        self::STATUS_ORDER_PLACED => [
            'step' => 1,
            'code' => self::STATUS_ORDER_PLACED,
            'label' => 'Order Placed',
            'meaning' => 'Customer successfully placed the order',
            'badge' => 'badge--warning',
            'icon' => '📝',
        ],
        self::STATUS_ORDER_CONFIRMED => [
            'step' => 2,
            'code' => self::STATUS_ORDER_CONFIRMED,
            'label' => 'Order Confirmed',
            'meaning' => 'Admin accepted/confirmed the order',
            'badge' => 'badge--primary',
            'icon' => '✓',
        ],
        self::STATUS_PROCESSING => [
            'step' => 3,
            'code' => self::STATUS_PROCESSING,
            'label' => 'Processing',
            'meaning' => 'Order is being prepared',
            'badge' => 'badge--secondary',
            'icon' => '⚙️',
        ],
        self::STATUS_READY_TO_SHIP => [
            'step' => 4,
            'code' => self::STATUS_READY_TO_SHIP,
            'label' => 'Ready to Ship',
            'meaning' => 'Product is packed and ready',
            'badge' => 'badge--secondary',
            'icon' => '📦',
        ],
        self::STATUS_SHIPPED => [
            'step' => 5,
            'code' => self::STATUS_SHIPPED,
            'label' => 'Shipped',
            'meaning' => 'Order handed over to courier',
            'badge' => 'badge--warning',
            'icon' => '🚚',
        ],
        self::STATUS_DELIVERED => [
            'step' => 6,
            'code' => self::STATUS_DELIVERED,
            'label' => 'Delivered',
            'meaning' => 'Customer received the order',
            'badge' => 'badge--success',
            'icon' => '🎉',
        ],
        self::STATUS_CANCELLED => [
            'step' => 7,
            'code' => self::STATUS_CANCELLED,
            'label' => 'Cancelled',
            'meaning' => 'Order was cancelled',
            'badge' => 'badge--danger',
            'icon' => '✕',
        ],
        self::STATUS_RETURNED => [
            'step' => 8,
            'code' => self::STATUS_RETURNED,
            'label' => 'Returned',
            'meaning' => 'Product was returned',
            'badge' => 'badge--danger',
            'icon' => '↩',
        ],
        self::STATUS_REFUNDED => [
            'step' => 9,
            'code' => self::STATUS_REFUNDED,
            'label' => 'Refunded',
            'meaning' => 'Refund completed',
            'badge' => 'badge--secondary',
            'icon' => '💰',
        ],
    ];

    protected $fillable = [
        'uuid',
        'order_number',
        'user_id',
        'status',
        'payment_status',
        'payment_method',
        'payment_gateway',
        'gateway_order_id',
        'gateway_payment_id',
        'gateway_signature',
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
        
        // Tracking & Courier
        'shipping_method',
        'tracking_number',
        'courier_name',
        'courier_id',
        'courier_tracking_url',
        'courier_contact_number',
        'courier_person_name',
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

    protected $appends = [
        'tracking_url',
        'status_label',
        'status_details',
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

    /**
     * Get the courier partner assigned to this order.
     */
    public function courier(): BelongsTo
    {
        return $this->belongsTo(Courier::class);
    }

    /**
     * Get human-friendly label for current status.
     */
    public function getStatusLabelAttribute(): string
    {
        $statusKey = $this->normalizeStatus($this->status);
        return static::STATUSES[$statusKey]['label'] ?? ucfirst(str_replace('_', ' ', $this->status));
    }

    /**
     * Get full status details (step 1-9, label, meaning, badge class, icon).
     */
    public function getStatusDetailsAttribute(): array
    {
        $statusKey = $this->normalizeStatus($this->status);
        return static::STATUSES[$statusKey] ?? [
            'step' => 0,
            'code' => $this->status,
            'label' => ucfirst(str_replace('_', ' ', $this->status)),
            'meaning' => '',
            'badge' => 'badge--secondary',
            'icon' => '📦',
        ];
    }

    /**
     * Normalize legacy statuses to standard status keys.
     */
    public function normalizeStatus(?string $status): string
    {
        $status = strtolower(trim((string)$status));
        if ($status === 'pending') return static::STATUS_ORDER_PLACED;
        if ($status === 'confirmed') return static::STATUS_ORDER_CONFIRMED;
        return $status;
    }
}
