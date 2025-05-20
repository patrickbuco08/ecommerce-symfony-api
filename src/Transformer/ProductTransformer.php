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
            (new CategoryTransformer())->transform($product->getCategory()),
            (new TagTransformer())->transformCollection($product->getTags()->toArray()),
            (new ReviewTransformer())->transformCollection($product->getReviews()->toArray()),
            (new ProductImageTransformer())->transformCollection($product->getImages()->toArray()),
            (array) (new UserTransformer())->transform($product->getUser()),
            $product->getCreatedAt()->format('Y-m-d H:i:s')
        );
    }

    public function transformCollection(array $products): array
    {
        return array_map(fn(Product $product) => $this->transform($product), $products);
    }
}
