<?php

namespace Bocum\Service\Payment;

class PaypalPaymentStrategy implements PaymentStrategyInterface
{
    public function pay(float $amount): string
    {
        // Simulating PayPal payment processing
        return "Paid ₱{$amount} via PayPal.";
    }
}
