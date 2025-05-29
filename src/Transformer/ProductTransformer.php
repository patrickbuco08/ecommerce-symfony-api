<?php

namespace Bocum\Transformer;

use Bocum\Dto\ProductDto;
use Bocum\Entity\Product;
use Bocum\Transformer\UserTransformer;
use Bocum\Transformer\CategoryTransformer;
use Bocum\Transformer\TagTransformer;
use Bocum\Transformer\ReviewTransformer;
use Bocum\Transformer\ProductImageTransformer;

class ProductTransformer
{
    public function __construct(
        private CategoryTransformer $categoryTransformer,
        private TagTransformer $tagTransformer,
        private ReviewTransformer $reviewTransformer,
        private ProductImageTransformer $productImageTransformer,
        private UserTransformer $userTransformer
    ) {}

    public function transform(Product $product): ProductDto
    {
        return new ProductDto(
            $product->getId(),
            $product->getTitle(),
            $product->getSlug(),
            $product->getDescription(),
            $product->getPrice(),
            $product->getRating(),
            $product->getStock(),
            $this->categoryTransformer->transform($product->getCategory()),
            $this->tagTransformer->transformCollection($product->getTags()->toArray()),
            $this->reviewTransformer->transformCollection($product->getReviews()->toArray()),
            $this->productImageTransformer->transformCollection($product->getImages()->toArray()),
            (array) $this->userTransformer->transform($product->getUser()),
            $product->getCreatedAt()->format('Y-m-d H:i:s')
        );
    }

    public function transformCollection(array $products): array
    {
        return array_map(fn(Product $product) => $this->transform($product), $products);
    }
}

