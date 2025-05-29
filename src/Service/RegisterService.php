<?php

namespace Bocum\Service;

use RuntimeException;
use Bocum\Entity\User;
use InvalidArgumentException;
use Bocum\Transformer\UserTransformer;
use Bocum\Dto\Request\UserRegisterData;
use Doctrine\ORM\EntityManagerInterface;

class RegisterService
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private UserService $userService,
        private UserTransformer $userTransformer
    ) {}

    public function registerUser(array $data): User
    {
        if (empty($data['email']) || empty($data['password'])) {
            throw new InvalidArgumentException('Email and password are required');
        }

        $existingUser = $this->entityManager->getRepository(User::class)->findOneBy(['email' => $data['email']]);

        if ($existingUser) {
            throw new RuntimeException('User already exists');
        }

        $userRegisterData = new UserRegisterData(
            $data['first_name'] ?? null,
            $data['last_name'] ?? null,
            $data['phone'] ?? null,
            $data['email'],
            $data['password'],
            $data['roles'] ?? null
        );

        return $this->userService->create($userRegisterData);
    }
}
