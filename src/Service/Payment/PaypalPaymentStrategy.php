<?php

namespace Bocum\Service\Payment;

use Bocum\Dto\PaymentDto;
use Bocum\Enum\PaymentOption;

class PaypalPaymentStrategy implements PaymentStrategyInterface
{
    public function pay(PaymentDto $payment): PaymentDto
    {
        $payment->setPaymentOption(PaymentOption::PAYPAL);
        $payment->setTransactionId('PAYPAL_192837465');
        $payment->setAdditionalData([
            'message' => 'thank you for using paypal'
        ]);

        return $payment;
    }
}
