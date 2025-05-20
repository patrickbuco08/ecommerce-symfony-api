<?php

namespace Bocum\Command;

use Doctrine\DBAL\Connection;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(
    name: 'app:reset-demo-tables',
    description: 'Deletes all demo data from users, products, categories, tags, reviews, and product_images tables.'
)]
class ResetDemoTablesCommand extends Command
{
    public function __construct(private EntityManagerInterface $entityManager)
    {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $conn = $this->entityManager->getConnection();
        $platform = $conn->getDatabasePlatform();
        $output->writeln('<info>Resetting demo tables...</info>');
        // Disable foreign key checks
        if ($platform->getName() === 'mysql') {
            $conn->executeStatement('SET FOREIGN_KEY_CHECKS=0');
        } elseif ($platform->getName() === 'sqlite') {
            $conn->executeStatement('PRAGMA foreign_keys = OFF');
        }
        // Truncate tables
        $tables = [
            'product_images',
            'reviews',
            'tags',
            'products',
            'categories',
            'users',
        ];
        foreach ($tables as $table) {
            $conn->executeStatement($platform->getTruncateTableSQL($table, true));
            $output->writeln("<comment>Truncated table:</comment> $table");
        }
        // Re-enable foreign key checks
        if ($platform->getName() === 'mysql') {
            $conn->executeStatement('SET FOREIGN_KEY_CHECKS=1');
        } elseif ($platform->getName() === 'sqlite') {
            $conn->executeStatement('PRAGMA foreign_keys = ON');
        }
        $output->writeln('<info>All demo tables have been reset.</info>');
        return Command::SUCCESS;
    }
}
