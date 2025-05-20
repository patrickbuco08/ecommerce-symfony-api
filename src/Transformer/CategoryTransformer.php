<?php

namespace Bocum\Transformer;

use Bocum\Entity\Category;

class CategoryTransformer
{
    public function transform(Category $category): array
    {
        return [
            'id' => $category->getId(),
            'name' => $category->getName(),
            'slug' => $category->getSlug(),
        ];
    }

    public function transformCollection(array $categories): array
    {
        return array_map(fn(Category $category) => $this->transform($category), $categories);
    }
}
