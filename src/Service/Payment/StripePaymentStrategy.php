<?php

namespace Bocum\Service\Payment;

class StripePaymentStrategy implements PaymentStrategyInterface
{
    public function pay(float $amount): string
    {
        // Simulating Stripe payment processing
        return "Paid ₱{$amount} via Stripe.";
    }
}
