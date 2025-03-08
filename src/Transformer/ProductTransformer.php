<?php

namespace Bocum\Transformer;

use Bocum\Dto\ProductDto;
use Bocum\Entity\Product;

class ProductTransformer
{
    public function transform(Product $product): ProductDto
    {
        return new ProductDto(
            $product->getId(),
            $product->getTitle(),
            $product->getDescription(),
            $product->getPrice(),
            $product->getRating(),
            $product->getStock(),
            [
                'id' => $product->getCategory()->getId(),
                'name' => $product->getCategory()->getName(),
                'slug' => $product->getCategory()->getSlug(),
            ],
            array_map(fn($item) => [
                'id' => $item->getId(),
                'name' => $item->getName(),
            ], $product->getTags()->toArray()),
            array_map(fn($review) => [
                'id' => $review->getId(),
                'rating' => $review->getRating(),
                'comment' => $review->getComment(),
            ], $product->getReviews()->toArray()),
            array_map(fn($image) => [
                'id' => $image->getId(),
                'name' => $image->getName(),
            ], $product->getImages()->toArray()),
            $product->getCreatedAt()->format('Y-m-d H:i:s')
        );
    }

    public function transformCollection(array $products): array
    {
        return array_map(fn(Product $product) => $this->transform($product), $products);
    }
}
