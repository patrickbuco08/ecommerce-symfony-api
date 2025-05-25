<?php

namespace Bocum\Factory;

use Bocum\Entity\Product;
use Bocum\Entity\Category;
use Bocum\Entity\User;

class ProductFactory
{
    public function create(
        string $title,
        Category $category,
        ?string $description,
        float $price,
        int $stock,
        float $rating,
        User $user,
    ): Product {
        $slug = $this->generateSlug($title);

        return (new Product())
            ->setTitle($title)
            ->setSlug($slug)
            ->setCategory($category)
            ->setDescription($description)
            ->setPrice($price)
            ->setStock($stock)
            ->setRating($rating)
            ->setUser($user);
    }

    private function generateSlug(string $title): string
    {
        // Convert to lowercase
        $slug = strtolower($title);

        // Remove special characters except alphanumeric and spaces
        $slug = preg_replace('/[^a-z0-9\s-]/', '', $slug);

        // Replace spaces with dashes
        $slug = preg_replace('/\s+/', '-', $slug);

        // Append a unique ID
        return $slug . '-' . uniqid();
    }
}
