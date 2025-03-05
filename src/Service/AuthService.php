<?php

namespace Bocum\Service;

use Bocum\Entity\User;
use Doctrine\ORM\EntityManagerInterface;

class AuthService
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

    public function register(array $data): array
    {
        if (!isset($data['email'], $data['password'])) {
            return ['error' => 'Email and password are required'];
        }

        $existingUser = $this->entityManager->getRepository(User::class)->findOneBy(['email' => $data['email']]);

        if ($existingUser) {
            return ['error' => 'User already exists'];
        }

        return $this->userService->create($data);
    }
}
