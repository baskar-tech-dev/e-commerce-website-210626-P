<?php

namespace App\Repositories;

use App\Models\Payment;

class PaymentRepository implements PaymentRepositoryInterface
{
    /**
     * Create a new payment record or update an existing one.
     *
     * @param array $attributes
     * @param array $values
     * @return Payment
     */
    public function updateOrCreatePayment(array $attributes, array $values): Payment
    {
        return Payment::updateOrCreate($attributes, $values);
    }

    /**
     * Find a payment by its gateway order ID.
     *
     * @param string $gatewayOrderId
     * @return Payment|null
     */
    public function findByGatewayOrderId(string $gatewayOrderId): ?Payment
    {
        return Payment::where('gateway_order_id', $gatewayOrderId)->first();
    }

    /**
     * Find a payment by its gateway payment ID.
     *
     * @param string $gatewayPaymentId
     * @return Payment|null
     */
    public function findByGatewayPaymentId(string $gatewayPaymentId): ?Payment
    {
        return Payment::where('gateway_payment_id', $gatewayPaymentId)->first();
    }
}
