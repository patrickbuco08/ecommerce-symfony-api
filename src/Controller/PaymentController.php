<?php

namespace Bocum\Controller;

use Bocum\Service\PaymentService;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Http\Attribute\CurrentUser;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

#[Route('/api/pay')]
class PaymentController extends AbstractController
{
    public function __construct(
        private PaymentService $paymentService
    ) {}

    #[Route('', name: 'make_payment', methods: ['POST'])]
    public function pay(Request $request,  #[CurrentUser] UserInterface $user): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        $result = $this->paymentService->processPayment($data, $user);

        return new JsonResponse($result, isset($result['error']) ? JsonResponse::HTTP_BAD_REQUEST : JsonResponse::HTTP_OK);
    }
}
