<?php

namespace Bocum\Service;

use Bocum\Entity\Order;
use Bocum\Entity\OrderItem;
use Bocum\Entity\Product;
use Bocum\Enum\OrderStatus;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Security\Core\User\UserInterface;

class OrderService
{
    private EntityManagerInterface $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    public function createOrder(UserInterface $user, array $data): array
    {
        if (!isset($data['items']) || !is_array($data['items'])) {
            return ['error' => 'Invalid order data'];
        }

        $order = new Order();
        $order->setUser($user);
        $total = 0;

        foreach ($data['items'] as $itemData) {
            $product = $this->entityManager->getRepository(Product::class)->find($itemData['product_id']);
            if (!$product) {
                return ['error' => 'Product not found'];
            }

            $orderItem = new OrderItem();
            $orderItem->setProduct($product);
            $orderItem->setQuantity($itemData['quantity']);
            $orderItem->setPrice($product->getPrice() * $itemData['quantity']);
            $total += $orderItem->getPrice();

            $order->addItem($orderItem);
        }

        $order->setTotal($total);
        $this->entityManager->persist($order);
        $this->entityManager->flush();

        return ['message' => 'Order placed successfully', 'order_id' => $order->getId()];
    }

    public function getUserOrders(UserInterface $user): array
    {
        return array_map(fn($order) => [
            'id' => $order->getId(),
            'status' => $order->getStatus()->value,
            'total' => $order->getTotal(),
            'createdAt' => $order->getCreatedAt()->format('Y-m-d H:i:s'),
        ], $user->getOrders()->toArray());
    }
}
