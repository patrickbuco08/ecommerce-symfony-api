<?php

namespace Bocum\Service\Payment;

use Bocum\Dto\PaymentDto;

class PaymentProcessor
{
    public function __construct(private PaymentStrategyInterface $paymentStrategy) {}

    public function processPayment(PaymentDto $paymentDto): PaymentDto
    {
        return $this->paymentStrategy->pay($paymentDto);
    }
}
