<?php

namespace Bocum\Service\Payment;

use Bocum\Dto\PaymentDto;

interface PaymentStrategyInterface
{
    public function pay(PaymentDto $payment);
}
