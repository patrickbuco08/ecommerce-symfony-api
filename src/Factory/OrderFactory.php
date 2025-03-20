<?php

namespace Bocum\Factory;

use Bocum\Dto\OrderData;
use Bocum\Entity\Order;
use Bocum\Entity\Product;
use Bocum\Entity\OrderItem;
use Doctrine\ORM\EntityManagerInterface;

class OrderFactory
{
    public function __construct(private EntityManagerInterface $entityManager) {}

    public function createOrder(OrderData $od): Order
    {
        $order = new Order();
        $order->setUser($od->user);
        $order->setGuestName($od->guestName);
        $order->setGuestPhone($od->guestPhone);
        $total = 0;

        foreach ($od->items as $itemData) {
            $product = $this->entityManager->getRepository(Product::class)->find($itemData['product_id']);

            if (!$product) {
                throw new \InvalidArgumentException('Product not found');
            }

            $orderItem = $this->createOrderItem($product, $itemData['quantity']);
            $total += $orderItem->getPrice();

            $order->addItem($orderItem);
        }

        $order->setTotal($total);

        return $order;
    }

    private function createOrderItem(Product $product, int $quantity): OrderItem
    {
        $orderItem = new OrderItem();
        $orderItem->setProduct($product);
        $orderItem->setQuantity($quantity);
        $orderItem->setPrice($product->getPrice() * $quantity);

        return $orderItem;
    }
}
