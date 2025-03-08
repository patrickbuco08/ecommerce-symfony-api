<?php

namespace Bocum\Factory;

use Bocum\Entity\Product;
use Bocum\Entity\Category;

class ProductFactory
{
    public static function create(
        string $title,
        Category $category,
        ?string $description,
        float $price,
        int $stock,
        float $rating
    ): Product {
        return (new Product())
            ->setTitle($title)
            ->setCategory($category)
            ->setDescription($description)
            ->setPrice($price)
            ->setStock($stock)
            ->setRating($rating);
    }
}
