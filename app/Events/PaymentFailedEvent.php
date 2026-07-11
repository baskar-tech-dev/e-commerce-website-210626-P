<?php

namespace App\Events;

use App\Models\Order;
use App\Models\Payment;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class PaymentFailedEvent
{
    use Dispatchable, SerializesModels;

    public $order;
    public $payment;
    public $reason;

    public function __construct(Order $order, Payment $payment, string $reason)
    {
        $this->order = $order;
        $this->payment = $payment;
        $this->reason = $reason;
    }
}
