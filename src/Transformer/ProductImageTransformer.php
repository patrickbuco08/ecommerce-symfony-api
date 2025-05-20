<?php

namespace Bocum\Transformer;

use Bocum\Entity\ProductImage;

class ProductImageTransformer
{
    public function transform(ProductImage $image): array
    {
        return [
            'id' => $image->getId(),
            'name' => $image->getName(),
        ];
    }

    public function transformCollection(array $images): array
    {
        return array_map(fn(ProductImage $image) => $this->transform($image), $images);
    }
}
