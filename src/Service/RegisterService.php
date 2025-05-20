<?php

namespace Bocum\Service;

use Bocum\Entity\User;
use Doctrine\ORM\EntityManagerInterface;

use Bocum\Dto\Request\UserRegisterData;
use Bocum\Transformer\UserTransformer;

class RegisterService
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private UserService $userService,
        private UserTransformer $userTransformer
    ) {}

    public function registerUser(UserRegisterData $data): User
    {
        if (empty($data->email) || empty($data->password)) {
            throw new \InvalidArgumentException('Email and password are required');
        }

        $existingUser = $this->entityManager->getRepository(User::class)->findOneBy(['email' => $data->email]);

        if ($existingUser) {
            throw new \RuntimeException('User already exists');
        }

        $user = $this->userService->create($data);

        if (!$user instanceof User) {
            throw new \RuntimeException('User registration failed');
        }

        return $user;
    }
}
