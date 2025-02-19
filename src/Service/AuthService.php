<?php

namespace Bocum\Service;

use Bocum\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class AuthService
{
    private EntityManagerInterface $entityManager;
    private UserPasswordHasherInterface $passwordHasher;
    private ValidatorInterface $validator;

    public function __construct(
        EntityManagerInterface $entityManager,
        UserPasswordHasherInterface $passwordHasher,
        ValidatorInterface $validator
    ) {
        $this->entityManager = $entityManager;
        $this->passwordHasher = $passwordHasher;
        $this->validator = $validator;
    }

    public function registerUser(array $data): array
    {
        if (!isset($data['email'], $data['password'])) {
            return ['error' => 'Email and password are required'];
        }

        $existingUser = $this->entityManager->getRepository(User::class)->findOneBy(['email' => $data['email']]);
        if ($existingUser) {
            return ['error' => 'User already exists'];
        }

        $user = new User();
        $user->setEmail($data['email']);
        $hashedPassword = $this->passwordHasher->hashPassword($user, $data['password']);
        $user->setPassword($hashedPassword);
        $user->setRoles($data['roles'] ?? ['ROLE_USER']);

        $errors = $this->validator->validate($user);
        if (count($errors) > 0) {
            return ['errors' => array_map(fn($e) => $e->getMessage(), iterator_to_array($errors))];
        }

        $this->entityManager->persist($user);
        $this->entityManager->flush();

        return ['message' => 'User registered successfully', 'user_id' => $user->getId()];
    }
}
