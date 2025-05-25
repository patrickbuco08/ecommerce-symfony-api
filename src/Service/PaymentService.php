<?php

namespace Bocum\Service;

use Bocum\Entity\Order;
use Bocum\Dto\PaymentDto;
use Bocum\Enum\PaymentOption;
use Bocum\Factory\PaymentFactory;
use Bocum\Transformer\OrderTransformer;
use Doctrine\ORM\EntityManagerInterface;
use Bocum\Service\Payment\PaymentProcessor;
use Symfony\Bundle\SecurityBundle\Security;
use Bocum\Service\Payment\GcashPaymentStrategy;
use Bocum\Service\Payment\PaypalPaymentStrategy;
use Bocum\Service\Payment\StripePaymentStrategy;

class PaymentService
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private OrderTransformer $orderTransformer,
        private PaymentFactory $paymentFactory,
        private Security $security
    ) {}

    public function processPayment(array $data)
    {
        $user = $this->security->getUser();
        $orderId = $data['order_id'] ?? null;
        $paymentMethod = PaymentOption::tryFrom($data['payment_method']);
        $amount = $data['amount'] ?? null;

        if (!$paymentMethod) {
            return ['error' => 'Invalid Payment Method'];
        }

        $order = $this->entityManager->getRepository(Order::class)->findOneBy(['id' => $orderId, 'user' => $user]);

        if (!$order) {
            return ['error' => 'Order not found'];
        }

        if ($order->getPaidAt() !== null) {
            return ['error' => 'Payment already processed'];
        }

        $paymentStrategy = match ($paymentMethod->value) {
            'paypal' => new PaypalPaymentStrategy(),
            'stripe' => new StripePaymentStrategy(),
            'gcash' => new GcashPaymentStrategy(),
            default => throw new \Exception('Invalid payment method'),
        };

        // Process payment
        $processor = new PaymentProcessor($paymentStrategy);
        $result = $processor->processPayment(new PaymentDto($order, $user, $amount));

        // Update order with payment details
        $order->setPaymentMethod($result->paymentOption);
        $order->setPaymentTransactionId($result->transactionId);
        $order->setPaidAt(new \DateTimeImmutable());

        // save to payments table
        $paymentData = $this->paymentFactory->create($result);
        $this->entityManager->persist($paymentData);
        $this->entityManager->flush();

        return ['message' => 'success'];
    }
}
