<?php

namespace Bocum\Service;

use Bocum\Entity\User;
use Bocum\Entity\Order;
use Bocum\Entity\Product;
use Bocum\Entity\OrderItem;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Security\Core\User\UserInterface;

class OrderService
{
    private EntityManagerInterface $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    public function create(UserInterface $user, array $data): array
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

    public function getUserOrders(User $user): array
    {
        return array_map(fn($order) => $this->orderToArray($order), $user->getOrders()->toArray());
    }

    public function orderToArray(Order $order)
    {
        return [
            'id' => $order->getId(),
            'user' => $order->getUser()->getEmail(),
            'status' => $order->getStatus()->value,
            'total' => $order->getTotal(),
            'createdAt' => $order->getCreatedAt()->format('Y-m-d H:i:s'),
            'items' => array_map(fn($item) => [
                'product' => $item->getProduct()->getTitle(),
                'quantity' => $item->getQuantity(),
                'price' => $item->getPrice(),
            ], $order->getItems()->toArray())
        ];
    }
}
