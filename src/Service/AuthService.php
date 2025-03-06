<?php

namespace Bocum\Service;

use Bocum\Entity\User;
use Bocum\Event\UserRegisteredEvent;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

class AuthService
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private UserService $userService,
        private EventDispatcherInterface $eventDispatcher
    ) {}

    public function register(array $data)
    {
        if (!isset($data['email'], $data['password'])) {
            return ['error' => 'Email and password are required'];
        }

        $existingUser = $this->entityManager->getRepository(User::class)->findOneBy(['email' => $data['email']]);

        if ($existingUser) {
            return ['error' => 'User already exists'];
        }

        $user = $this->userService->create($data);

        $event = new UserRegisteredEvent($user);
        $this->eventDispatcher->dispatch($event);

        return ['success' => 'User registered successfully'];
    }
}
