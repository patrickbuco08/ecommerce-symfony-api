<?php

namespace Bocum\Service\Payment;

interface PaymentStrategyInterface
{
    public function pay(float $amount): string;
}
