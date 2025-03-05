<?php

namespace Bocum\Service;

use Bocum\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;

class RegisterService
{
    private EntityManagerInterface $entityManager;
    private UserService $userService;

    public function __construct(
        EntityManagerInterface $entityManager,
        UserService $userService
    ) {
        $this->entityManager = $entityManager;
        $this->userService = $userService;
    }

    public function registerUser(array $data): JsonResponse
    {
        if (!isset($data['email'], $data['password'])) {
            return new JsonResponse(['error' => 'Email and password are required']);
        }

        $existingUser = $this->entityManager->getRepository(User::class)->findOneBy(['email' => $data['email']]);

        if ($existingUser) {
            return new JsonResponse(['error' => 'User already exists']);
        }

        $result = $this->userService->create($data);
        return new JsonResponse($result);
    }
}
