<?php

namespace Bocum\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Bocum\Service\Payment\PaymentProcessor;
use Bocum\Service\Payment\PaypalPaymentStrategy;
use Bocum\Service\Payment\StripePaymentStrategy;
use Bocum\Service\Payment\GcashPaymentStrategy;

class PaymentController extends AbstractController
{
    #[Route('/pay/{method}/{amount}', name: 'make_payment')]
    public function pay(string $method, float $amount): JsonResponse
    {
        // Select payment method dynamically
        $paymentStrategy = match ($method) {
            'paypal' => new PaypalPaymentStrategy(),
            'stripe' => new StripePaymentStrategy(),
            'gcash' => new GcashPaymentStrategy(),
            default => throw new \Exception('Invalid payment method'),
        };

        // Process payment
        $processor = new PaymentProcessor($paymentStrategy);
        $result = $processor->processPayment($amount);

        return $this->json(['message' => $result]);
    }
}
