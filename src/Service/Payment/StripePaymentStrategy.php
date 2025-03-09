<?php

namespace Bocum\Service\Payment;

use Bocum\Dto\PaymentDto;
use Bocum\Enum\PaymentOption;

class StripePaymentStrategy implements PaymentStrategyInterface
{
    public function pay(PaymentDto $payment): PaymentDto
    {
        $payment->setPaymentOption(PaymentOption::STRIPE);
        $payment->setTransactionId('STRIPE_192837465');
        $payment->setAdditionalData([
            'message' => 'thank you for using STRIPE'
        ]);

        return $payment;
    }
}
