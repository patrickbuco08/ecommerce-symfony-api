<?php

namespace Bocum\Service;

use Bocum\Entity\User;
use Bocum\Dto\Request\UserRegisterData;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class UserService
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private UserPasswordHasherInterface $passwordHasher,
        private ValidatorInterface $validator
    ) {}

    public function create(UserRegisterData $data): User
    {
        $user = new User();
        $user->setFirstName($data->firstName);
        $user->setLastName($data->lastName);
        $user->setPhone($data->phone);
        $user->setEmail($data->email);
        $hashedPassword = $this->passwordHasher->hashPassword($user, $data->password);
        $user->setPassword($hashedPassword);
        $user->setRoles($data->roles ?? ['ROLE_USER']);

        $errors = $this->validator->validate($user);
        if (count($errors) > 0) {
            $errorMessages = array_map(fn($e) => $e->getMessage(), iterator_to_array($errors));
            throw new \RuntimeException(implode('; ', $errorMessages));
        }

        $this->entityManager->persist($user);
        $this->entityManager->flush();

        return $user;
    }

    public function update(User $user, array $data): array
    {
        if (isset($data['email'])) {
            $user->setEmail($data['email']);
        }

        $this->entityManager->flush();

        return ['message' => 'Profile updated successfully'];
    }
}
