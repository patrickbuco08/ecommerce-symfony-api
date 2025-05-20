<?php

namespace Bocum\Command;

use Bocum\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Bocum\Command\UserData;

#[AsCommand(
    name: 'app:populate-users',
    description: 'Populates the users table with default users.'
)]
class PopulateUsersCommand extends Command
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private UserPasswordHasherInterface $passwordHasher
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        foreach (UserData::USERS as $userData) {
            $existing = $this->entityManager->getRepository(User::class)->findOneBy(['email' => $userData['email']]);
            if ($existing) {
                $output->writeln("User {$userData['email']} already exists, skipping.");
                continue;
            }
            $user = new User();
            $user->setEmail($userData['email']);
            $user->setFirstName($userData['firstName']);
            $user->setLastName($userData['lastName']);
            $user->setPhone($userData['phone']);
            $user->setRoles(['ROLE_USER']);
            $hashedPassword = $this->passwordHasher->hashPassword($user, $userData['password']);
            $user->setPassword($hashedPassword);
            $this->entityManager->persist($user);
            $output->writeln("Added user: {$userData['email']}");
        }
        $this->entityManager->flush();
        $output->writeln('Users populated successfully.');
        return Command::SUCCESS;
    }
}
