<?php

namespace Bocum\Service\Payment;

class GcashPaymentStrategy implements PaymentStrategyInterface
{
    public function pay(float $amount): string
    {
        // Simulating GCash payment processing
        return "Paid ₱{$amount} via GCash.";
    }
}
