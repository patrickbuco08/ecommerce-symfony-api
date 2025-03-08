<?php

namespace Bocum\Transformer;

use Bocum\Dto\OrderDto;
use Bocum\Dto\ProductDto;
use Bocum\Entity\Order;
use Bocum\Entity\Product;

class OrderTransformer
{
    public function transform(Order $order): OrderDto
    {
        return new OrderDto(
            $order->getId(),
            $order->getUser()->getEmail(),
            $order->getStatus()->value,
            $order->getTotal(),
            $order->getCreatedAt()->format('Y-m-d H:i:s'),
            array_map(fn($item) => [
                'product' => $item->getProduct()->getTitle(),
                'quantity' => $item->getQuantity(),
                'price' => $item->getPrice(),
            ], $order->getItems()->toArray())
        );
    }

    public function transformCollection(array $orders): array
    {
        return array_map(fn(Order $order) => $this->transform($order), $orders);
    }
}
