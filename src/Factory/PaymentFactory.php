<?php

namespace Bocum\Factory;

use Bocum\Dto\PaymentDto;
use Bocum\Entity\Payment;

class PaymentFactory
{
    public function create(PaymentDto $payment): Payment
    {
        return (new Payment())
            ->setOrder($payment->order)
            ->setUser($payment->user)
            ->setPaymentMethod($payment->paymentOption)
            ->setTransactionId($payment->transactionId)
            ->setAmount($payment->amount)
            ->setResponseData($payment->additionalData);
    }
}
