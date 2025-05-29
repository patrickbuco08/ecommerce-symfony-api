<?php

namespace Bocum\Transformer;

use Bocum\Entity\OrderItem;

class OrderItemTransformer
{
    public function __construct(private ProductTransformer $productTransformer) {}

    public function transform(OrderItem $item): array
    {
        return [
            'product' => $this->productTransformer->transform($item->getProduct()),
            'quantity' => $item->getQuantity(),
            'price' => $item->getPrice(),
        ];
    }

    /**
     * @param OrderItem[] $items
     * @return array
     */
    public function transformCollection(array $items): array
    {
        return array_map([$this, 'transform'], $items);
    }
}
