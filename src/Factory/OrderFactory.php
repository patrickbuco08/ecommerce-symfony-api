<?php

namespace Bocum\Factory;

use Bocum\Entity\User;
use Bocum\Entity\Order;
use Bocum\Entity\Product;
use Bocum\Entity\OrderItem;
use Doctrine\ORM\EntityManagerInterface;

class OrderFactory
{
    public function __construct(private EntityManagerInterface $entityManager) {}

    public function createOrder(User $user, array $items): Order
    {
        $order = new Order();
        $order->setUser($user);
        $total = 0;

        foreach ($items as $itemData) {
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
