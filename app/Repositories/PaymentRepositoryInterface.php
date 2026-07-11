<?php

namespace App\Repositories;

use App\Models\Payment;

interface PaymentRepositoryInterface
{
    /**
     * Create a new payment record or update an existing one.
     *
     * @param array $attributes
     * @param array $values
     * @return Payment
     */
    public function updateOrCreatePayment(array $attributes, array $values): Payment;

    /**
     * Find a payment by its gateway order ID.
     *
     * @param string $gatewayOrderId
     * @return Payment|null
     */
    public function findByGatewayOrderId(string $gatewayOrderId): ?Payment;

    /**
     * Find a payment by its gateway payment ID.
     *
     * @param string $gatewayPaymentId
     * @return Payment|null
     */
    public function findByGatewayPaymentId(string $gatewayPaymentId): ?Payment;
}
