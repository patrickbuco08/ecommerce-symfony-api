<?php

namespace Bocum\Transformer;

use Bocum\Dto\OrderDto;
use Bocum\Entity\Order;
use Bocum\Transformer\UserTransformer;

class OrderTransformer
{
    public function __construct(
        private UserTransformer $userTransformer,
        private OrderItemTransformer $orderItemTransformer
    ) {}

    public function transform(Order $order): OrderDto
    {
        return new OrderDto(
            $order->getId(),
            (array) $this->userTransformer->transform($order->getUser()),
            $order->getStatus()->value,
            $order->getTotal(),
            $order->getCreatedAt()->format('Y-m-d H:i:s'),
            $this->orderItemTransformer->transformCollection($order->getItems()->toArray())
        );
    }

    public function transformCollection(array $orders): array
    {
        return array_map(fn(Order $order) => $this->transform($order), $orders);
    }
}
