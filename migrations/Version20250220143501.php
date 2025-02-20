<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20250220143501 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Insert initial categories into the database';
    }

    public function up(Schema $schema): void
    {
        $this->addSql("
            INSERT INTO categories (name, slug, created_at) VALUES 
            ('Electronics', 'electronics', NOW()),
            ('Clothing', 'clothing', NOW()),
            ('Home & Kitchen', 'home-kitchen', NOW()),
            ('Beauty & Health', 'beauty-health', NOW()),
            ('Sports & Outdoor', 'sports-outdoor', NOW());
        ");
    }

    public function down(Schema $schema): void
    {
        $this->addSql("
            DELETE FROM categories 
            WHERE slug IN ('electronics', 'clothing', 'home-kitchen', 'beauty-health', 'sports-outdoor');
        ");
    }
}