<?php

namespace Bocum\Controller;

use Bocum\Entity\User;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Security\Http\Attribute\CurrentUser;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;
use OpenApi\Attributes as OA;

#[Route('/api', name: 'api_')]
class AuthController extends AbstractController
{
    #[OA\Post(
        path: '/api/login_check',
        summary: 'User login',
        description: 'Authenticates a user and returns a JWT token.',
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['username', 'password'],
                properties: [
                    new OA\Property(property: 'email', type: 'string', example: 'patrick.buco@lamudi.com'),
                    new OA\Property(property: 'password', type: 'string', example: 'password')
                ]
            )
        ),
        responses: [
            new OA\Response(
                response: 200,
                description: 'Successful authentication',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'token', type: 'string', example: 'eyJ0eXAiOiJKV1QiLCJhbGciOiJSUzI1NiJ9...')
                    ]
                )
            ),
            new OA\Response(response: 401, description: 'Invalid credentials')
        ],
        security: [['BearerAuth' => []]]
    )]
    #[Route('/login_check', name: 'api_login_check', methods: ['POST'])]
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
