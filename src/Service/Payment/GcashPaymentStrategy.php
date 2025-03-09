<?php

namespace Bocum\Service\Payment;

use Bocum\Dto\PaymentDto;
use Bocum\Enum\PaymentOption;

class GcashPaymentStrategy implements PaymentStrategyInterface
{
    public function pay(PaymentDto $payment): PaymentDto
    {
        $payment->setPaymentOption(PaymentOption::GCASH);
        $payment->setTransactionId('GCASH_192837465');
        $payment->setAdditionalData([
            'message' => 'thank you for using gCash'
        ]);

        return $payment;
    }
}
