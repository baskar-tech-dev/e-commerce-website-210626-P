<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PaymentReportLog extends Model
{
    use HasFactory;

    protected $table = 'payment_report_logs';

    protected $fillable = [
        'report_date',
        'triggered_at',
        'status',
        'channel',
        'recipients',
        'gross_revenue',
        'online_collection',
        'cod_amount',
        'net_settled',
        'gateway_fee',
        'gateway_tax',
        'orders_count',
        'transactions_count',
        'settlement_utr',
        'settlement_status',
        'summary_payload',
        'error_message',
        'duration_ms',
    ];

    protected $casts = [
        'report_date' => 'date:Y-m-d',
        'triggered_at' => 'datetime',
        'recipients' => 'array',
        'summary_payload' => 'array',
        'gross_revenue' => 'decimal:2',
        'online_collection' => 'decimal:2',
        'cod_amount' => 'decimal:2',
        'net_settled' => 'decimal:2',
        'gateway_fee' => 'decimal:2',
        'gateway_tax' => 'decimal:2',
        'orders_count' => 'integer',
        'transactions_count' => 'integer',
        'duration_ms' => 'integer',
    ];
}
