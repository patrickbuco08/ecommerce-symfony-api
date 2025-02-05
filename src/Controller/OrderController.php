<?php

namespace App\Controller;

use App\Entity\Order;
use App\Entity\OrderItem;
use App\Entity\Product;
use App\Enum\OrderStatus;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\Security\Http\Attribute\CurrentUser;
use App\Entity\User;

#[Route('/api/orders')]
class OrderController extends AbstractController
{
    #[Route('', name: 'create_order', methods: ['POST'])]
    #[IsGranted('ROLE_USER')]
    public function createOrder(
        Request $request,
        EntityManagerInterface $entityManager,
        #[CurrentUser] User $user
    ): JsonResponse {
        $data = json_decode($request->getContent(), true);

        if (!isset($data['items']) || !is_array($data['items'])) {
            return new JsonResponse(['error' => 'Invalid order data'], JsonResponse::HTTP_BAD_REQUEST);
        }

        $order = new Order();
        $order->setUser($user);
        $total = 0;

        foreach ($data['items'] as $itemData) {
            $product = $entityManager->getRepository(Product::class)->find($itemData['product_id']);

            if (!$product) {
                return new JsonResponse(['error' => 'Product not found'], JsonResponse::HTTP_NOT_FOUND);
            }

            $orderItem = new OrderItem();
            $orderItem->setProduct($product);
            $orderItem->setQuantity($itemData['quantity']);
            $orderItem->setPrice($product->getPrice() * $itemData['quantity']);
            $total += $orderItem->getPrice();

            $order->addItem($orderItem);
        }

        $order->setTotal($total);
        $entityManager->persist($order);
        $entityManager->flush();

        return new JsonResponse(['message' => 'Order placed successfully'], JsonResponse::HTTP_CREATED);
    }

    #[Route('', name: 'get_orders', methods: ['GET'])]
    #[IsGranted('ROLE_USER')]
    public function getUserOrders(
        #[CurrentUser] User $user
    ): JsonResponse {
        $orders = $user->getOrders();
        $data = [];

        foreach ($orders as $order) {
            $data[] = [
                'id' => $order->getId(),
                'status' => $order->getStatus()->value,
                'total' => $order->getTotal(),
                'createdAt' => $order->getCreatedAt()->format('Y-m-d H:i:s'),
                'items' => array_map(fn($item) => [
                    'product' => $item->getProduct()->getName(),
                    'quantity' => $item->getQuantity(),
                    'price' => $item->getPrice(),
                ], $order->getItems()->toArray())
            ];
        }

        return new JsonResponse($data);
    }

    #[Route('/{id}/status', name: 'update_order_status', methods: ['PUT'])]
    #[IsGranted('ROLE_ADMIN')]
    public function updateOrderStatus(
        Order $order,
        Request $request,
        EntityManagerInterface $entityManager
    ): JsonResponse {
        $data = json_decode($request->getContent(), true);

        if (!isset($data['status'])) {
            return new JsonResponse(['error' => 'Status is required'], JsonResponse::HTTP_BAD_REQUEST);
        }

        // Validate if the provided status exists in OrderStatus enum
        if (!OrderStatus::tryFrom($data['status'])) {
            return new JsonResponse(['error' => 'Invalid order status'], JsonResponse::HTTP_BAD_REQUEST);
        }

        $newStatus = OrderStatus::from($data['status']);

        // Enforce status transition rules
        if ($order->getStatus() === OrderStatus::COMPLETED) {
            return new JsonResponse(['error' => 'Completed orders cannot be changed'], JsonResponse::HTTP_BAD_REQUEST);
        }

        if ($order->getStatus() === OrderStatus::CANCELLED) {
            return new JsonResponse(['error' => 'Canceled orders cannot be changed'], JsonResponse::HTTP_BAD_REQUEST);
        }

        // Update the order status
        $order->setStatus($newStatus);
        $entityManager->flush();

        return new JsonResponse([
            'message' => 'Order status updated successfully',
            'new_status' => $order->getStatus()->value
        ]);
    }
}
