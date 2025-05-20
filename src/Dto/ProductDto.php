<?php

namespace Bocum\Dto;

class ProductDto
{
    public function __construct(
        public int $id,
        public string $title,
        public string $slug,
        public string $description,
        public float $price,
        public float $rating,
        public int $stock,
        public array $category,
        public array $tags,
        public array $reviews,
        public array $images,
        public array $owner,
        public string $createdAt
    ) {}
}
