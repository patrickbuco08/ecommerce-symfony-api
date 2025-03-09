<?php

namespace Bocum\Dto;

use Bocum\Entity\Order;
use Bocum\Entity\User;
use Bocum\Enum\PaymentOption;

class PaymentDto
{
    public function __construct(
        public Order $order,
        public User $user,
        public float $amount,
        public ?string $transactionId = null,
        public ?PaymentOption $paymentOption = null,
        public ?array $additionalData = []
    ) {}

    public function setTransactionId(string $transactionId): void
    {
        $this->transactionId = $transactionId;
    }

    public function setPaymentOption(PaymentOption $paymentOption): void
    {
        $this->paymentOption = $paymentOption;
    }

    public function setAdditionalData(array $additionalData): void
    {
        $this->additionalData = $additionalData;
    }
}
