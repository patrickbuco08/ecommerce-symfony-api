<?php

namespace Bocum\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Bocum\Service\RegisterService;
use Bocum\Transformer\UserTransformer;

class RegisterController extends AbstractController
{
    public function __construct(
        private RegisterService $registerService,
        private UserTransformer $userTransformer
    ) {}

    #[Route('/api/register', name: 'api_register', methods: ['POST'])]
    public function register(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        try {
            $user = $this->registerService->registerUser($data);
            return new JsonResponse($this->userTransformer->transform($user), JsonResponse::HTTP_CREATED);
        } catch (\Throwable $e) {
            return new JsonResponse([
                'success' => false,
                'error' => $e->getMessage(),
            ], JsonResponse::HTTP_BAD_REQUEST);
        }
    }
}
