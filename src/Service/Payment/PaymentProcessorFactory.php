<?php

namespace Bocum\Service\Payment;

class PaymentProcessorFactory
{
    public function create(PaymentStrategyInterface $strategy): PaymentProcessor
    {
        return new PaymentProcessor($strategy);
    }
}
