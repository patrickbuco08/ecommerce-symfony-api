<?php

namespace App\Controller;

use App\Entity\User;
use App\Entity\Order;
use App\Entity\Product;
use App\Entity\OrderItem;
use App\Enum\OrderStatus;
use App\Service\PdfGenerator;
use Pagerfanta\Pagerfanta;
use Doctrine\ORM\EntityManagerInterface;
use Pagerfanta\Doctrine\ORM\QueryAdapter;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\Security\Http\Attribute\CurrentUser;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

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

    #[Route('/status/{status}', name: 'get_orders_by_status', methods: ['GET'])]
    #[IsGranted('ROLE_ADMIN')]
    public function getOrdersByStatus(
        string $status,
        Request $request,
        EntityManagerInterface $entityManager
    ): JsonResponse {
        // Validate the status
        if (!OrderStatus::tryFrom($status)) {
            return new JsonResponse(['error' => 'Invalid order status'], JsonResponse::HTTP_BAD_REQUEST);
        }

        $queryBuilder = $entityManager->getRepository(Order::class)
            ->createQueryBuilder('o')
            ->where('o.status = :status')
            ->setParameter('status', $status)
            ->orderBy('o.createdAt', 'DESC');

        // Create a pagination adapter
        $adapter = new QueryAdapter($queryBuilder);
        $pagerfanta = new Pagerfanta($adapter);

        // Get the requested page, default to page 1
        $page = max(1, (int) $request->query->get('page', 1));
        $pagerfanta->setMaxPerPage(10); // Set items per page
        $pagerfanta->setCurrentPage($page);

        // Convert paginated results to JSON
        $data = array_map(fn($order) => [
            'id' => $order->getId(),
            'user' => $order->getUser()->getEmail(),
            'status' => $order->getStatus()->value,
            'total' => $order->getTotal(),
            'createdAt' => $order->getCreatedAt()->format('Y-m-d H:i:s'),
            'items' => array_map(fn($item) => [
                'product' => $item->getProduct()->getName(),
                'quantity' => $item->getQuantity(),
                'price' => $item->getPrice(),
            ], $order->getItems()->toArray())
        ], iterator_to_array($pagerfanta->getCurrentPageResults()));

        return new JsonResponse([
            'page' => $page,
            'total_pages' => $pagerfanta->getNbPages(),
            'total_orders' => $pagerfanta->getNbResults(),
            'orders' => $data
        ]);
    }

    #[Route('/my-orders/status/{status}', name: 'get_user_orders_by_status', methods: ['GET'])]
    #[IsGranted('ROLE_USER')]
    public function getUserOrdersByStatus(
        string $status,
        #[CurrentUser] User $user,
        Request $request,
        EntityManagerInterface $entityManager
    ): JsonResponse {
        if (!OrderStatus::tryFrom($status)) {
            return new JsonResponse(['error' => 'Invalid order status'], JsonResponse::HTTP_BAD_REQUEST);
        }

        $queryBuilder = $entityManager->getRepository(Order::class)
            ->createQueryBuilder('o')
            ->where('o.user = :user')
            ->andWhere('o.status = :status')
            ->setParameter('user', $user)
            ->setParameter('status', $status)
            ->orderBy('o.createdAt', 'DESC');

        $adapter = new QueryAdapter($queryBuilder);
        $pagerfanta = new Pagerfanta($adapter);

        $page = max(1, (int) $request->query->get('page', 1));
        $pagerfanta->setMaxPerPage(10);
        $pagerfanta->setCurrentPage($page);

        $data = array_map(fn($order) => [
            'id' => $order->getId(),
            'status' => $order->getStatus()->value,
            'total' => $order->getTotal(),
            'createdAt' => $order->getCreatedAt()->format('Y-m-d H:i:s'),
            'items' => array_map(fn($item) => [
                'product' => $item->getProduct()->getName(),
                'quantity' => $item->getQuantity(),
                'price' => $item->getPrice(),
            ], $order->getItems()->toArray())
        ], iterator_to_array($pagerfanta->getCurrentPageResults()));

        return new JsonResponse([
            'page' => $page,
            'total_pages' => $pagerfanta->getNbPages(),
            'total_orders' => $pagerfanta->getNbResults(),
            'orders' => $data
        ]);
    }

    #[Route('/my-orders/{id}/cancel', name: 'cancel_order', methods: ['PUT'])]
    #[IsGranted('ROLE_USER')]
    public function cancelOrder(
        Order $order,
        #[CurrentUser] User $user,
        EntityManagerInterface $entityManager
    ): JsonResponse {
        // Ensure the order belongs to the authenticated user
        if ($order->getUser() !== $user) {
            return new JsonResponse(['error' => 'You can only cancel your own orders'], JsonResponse::HTTP_FORBIDDEN);
        }

        // Ensure the order is still pending
        if ($order->getStatus() !== OrderStatus::PENDING) {
            return new JsonResponse(['error' => 'Only pending orders can be canceled'], JsonResponse::HTTP_BAD_REQUEST);
        }

        // Update order status to canceled
        $order->setStatus(OrderStatus::CANCELLED);
        $entityManager->flush();

        return new JsonResponse([
            'message' => 'Order canceled successfully',
            'new_status' => $order->getStatus()->value
        ]);
    }

    #[Route('/{id}/invoice', name: 'get_order_invoice', methods: ['GET'])]
    #[IsGranted('ROLE_USER')]
    public function getOrderInvoice(
        Order $order,
        #[CurrentUser] User $user,
        PdfGenerator $pdfGenerator
    ): Response {
        // Ensure the user owns the order or is an admin
        if ($order->getUser() !== $user && !in_array('ROLE_ADMIN', $user->getRoles())) {
            return new JsonResponse(['error' => 'You can only access your own invoices'], JsonResponse::HTTP_FORBIDDEN);
        }

        $pdfContent = $pdfGenerator->generateInvoice($order);

        $response = new StreamedResponse(function () use ($pdfContent) {
            echo $pdfContent;
        });

        $response->headers->set('Content-Type', 'application/pdf');
        $response->headers->set('Content-Disposition', 'attachment; filename="invoice-' . $order->getId() . '.pdf"');

        return $response;
    }


    #[Route('/{id}/generate-invoice', name: 'generate_order_invoice', methods: ['POST'])]
    #[IsGranted('ROLE_ADMIN')]
    public function generateInvoice(
        Order $order,
        PdfGenerator $pdfGenerator,
        EntityManagerInterface $entityManager,
        ParameterBagInterface $params
    ): JsonResponse {
        // Ensure invoice isn't already generated
        if ($order->getInvoicePath()) {
            return new JsonResponse(['error' => 'Invoice already exists'], JsonResponse::HTTP_CONFLICT);
        }

        $invoiceDir = $params->get('kernel.project_dir') . '/public/invoices';
        if (!is_dir($invoiceDir)) {
            mkdir($invoiceDir, 0777, true);
        }

        // Generate and save invoice
        $invoicePath = $pdfGenerator->generateAndSaveInvoice($order, $invoiceDir);
        $order->setInvoicePath('/invoices/' . basename($invoicePath));

        $entityManager->flush();

        return new JsonResponse([
            'message' => 'Invoice generated successfully',
            'invoice_path' => $order->getInvoicePath()
        ]);
    }

    #[Route('/{id}/download-invoice', name: 'download_order_invoice', methods: ['GET'])]
    #[IsGranted('ROLE_USER')]
    public function downloadInvoice(
        Order $order,
        #[CurrentUser] User $user
    ): Response {
        if ($order->getUser() !== $user && !in_array('ROLE_ADMIN', $user->getRoles())) {
            return new JsonResponse(['error' => 'You can only download your own invoices'], JsonResponse::HTTP_FORBIDDEN);
        }

        if (!$order->getInvoicePath()) {
            return new JsonResponse(['error' => 'Invoice not generated'], JsonResponse::HTTP_NOT_FOUND);
        }

        $filePath = $this->getParameter('kernel.project_dir') . '/public' . $order->getInvoicePath();

        if (!file_exists($filePath)) {
            return new JsonResponse(['error' => 'Invoice file not found'], JsonResponse::HTTP_NOT_FOUND);
        }

        return new BinaryFileResponse($filePath);
    }
}
