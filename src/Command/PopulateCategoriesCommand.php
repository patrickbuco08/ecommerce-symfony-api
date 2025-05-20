<?php

namespace Bocum\Command;

use Bocum\Entity\Category;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Bocum\Command\CategoryData;

#[AsCommand(
    name: 'app:populate-categories',
    description: 'Populates the categories table with default coffee shop categories.'
)]
class PopulateCategoriesCommand extends Command
{
    public function __construct(private EntityManagerInterface $entityManager)
    {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $categories = CategoryData::CATEGORIES;

        foreach ($categories as $catData) {
            $existing = $this->entityManager->getRepository(Category::class)->findOneBy(['slug' => $catData['slug']]);
            if ($existing) {
                $output->writeln("Category '{$catData['name']}' already exists, skipping.");
                continue;
            }
            $category = new Category();
            $category->setName($catData['name']);
            $category->setSlug($catData['slug']);
            $category->setCreatedAt(new \DateTimeImmutable());
            $this->entityManager->persist($category);
            $output->writeln("Added category: {$catData['name']}");
        }
        $this->entityManager->flush();
        $output->writeln('Categories populated successfully.');
        return Command::SUCCESS;
    }
}
