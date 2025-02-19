<?php

namespace Bocum\Service;

use Bocum\Entity\User;
use Doctrine\ORM\EntityManagerInterface;

class ProfileService
{
    private EntityManagerInterface $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    public function getUserProfile(User $user): array
    {
        return [
            'id' => $user->getId(),
            'email' => $user->getEmail(),
            'roles' => $user->getRoles(),
        ];
    }

    public function updateUserProfile(User $user, array $data): array
    {
        if (isset($data['email'])) {
            $user->setEmail($data['email']);
        }

        $this->entityManager->flush();

        return ['message' => 'Profile updated successfully'];
    }
}
