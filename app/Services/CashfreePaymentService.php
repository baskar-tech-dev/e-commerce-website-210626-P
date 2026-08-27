<?php

namespace App\Services;

/**
 * CashfreePaymentService
 * 
 * Extends the primary CashfreeService to maintain 100% backward compatibility
 * with all existing payment controllers, checkout flows, and test mocks.
 */
class CashfreePaymentService extends CashfreeService
{
    // Inherits all methods and properties from CashfreeService
}
