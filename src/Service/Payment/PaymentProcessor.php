<?php

namespace Bocum\Service\Payment;

class PaymentProcessor
{

    public function __construct(private PaymentStrategyInterface $paymentStrategy) {}

    public function processPayment(float $amount): string
    {
        return $this->paymentStrategy->pay($amount);
    }
}
