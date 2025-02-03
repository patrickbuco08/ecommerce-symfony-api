<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;
use Symfony\Component\Security\Http\Attribute\CurrentUser;
use App\Entity\User;

class AuthController extends AbstractController
{
    #[Route('/api/login_check', name: 'api_login_check', methods: ['POST'])]
    public function login(
        #[CurrentUser] ?User $user,
        JWTTokenManagerInterface $jwtManager
    ): JsonResponse {
        if (!$user) {
            return new JsonResponse(['error' => 'Invalid credentials'], JsonResponse::HTTP_UNAUTHORIZED);
        }

        return new JsonResponse(['token' => $jwtManager->create($user)]);
    }
}
