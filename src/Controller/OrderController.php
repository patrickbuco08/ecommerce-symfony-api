<?php

namespace Bocum\Controller;

use Bocum\Entity\User;
use Bocum\Entity\Order;
use Bocum\Entity\Product;
use Bocum\Entity\OrderItem;
use Bocum\Enum\OrderStatus;
use Pagerfanta\Pagerfanta;
use Bocum\Service\PdfGenerator;
use Bocum\Service\MailerService;
use Bocum\Service\OrderService;
use Doctrine\ORM\EntityManagerInterface;
use Pagerfanta\Doctrine\ORM\QueryAdapter;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\Security\Http\Attribute\CurrentUser;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\Security\Core\User\UserInterface;

#[Route('/api/orders')]
class OrderController extends AbstractController
{
    private OrderService $orderService;

    public function __construct(OrderService $orderService)
    {
        $this->orderService = $orderService;
    }

    #[Route('', name: 'create_order', methods: ['POST'])]
    #[IsGranted('ROLE_USER')]
    public function createOrder(Request $request, #[CurrentUser] UserInterface $user): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        $result = $this->orderService->createOrder($user, $data);

        return new JsonResponse($result, isset($result['error']) ? JsonResponse::HTTP_BAD_REQUEST : JsonResponse::HTTP_CREATED);
    }

    #[Route('', name: 'get_orders', methods: ['GET'])]
    #[IsGranted('ROLE_USER')]
    public function getUserOrders(#[CurrentUser] UserInterface $user): JsonResponse
    {
        return new JsonResponse($this->orderService->getUserOrders($user));
    }

    #[Route('/{id}/status', name: 'update_order_status', methods: ['PUT'])]
    #[IsGranted('ROLE_ADMIN')]
    public function updateOrderStatus(
        Order $order,
        Request $request,
        EntityManagerInterface $entityManager,
        PdfGenerator $pdfGenerator,
        ParameterBagInterface $params,
        MailerService $mailerService
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

        // Prevent changes to completed/canceled orders
        if (in_array($order->getStatus(), [OrderStatus::COMPLETED, OrderStatus::CANCELLED])) {
            return new JsonResponse(['error' => 'Completed or canceled orders cannot be changed'], JsonResponse::HTTP_BAD_REQUEST);
        }

        // Update the order status
        $order->setStatus($newStatus);

        // If order is marked as completed, generate an invoice
        if ($newStatus === OrderStatus::COMPLETED && !$order->getInvoicePath()) {
            $invoiceDir = $params->get('kernel.project_dir') . '/public/invoices';
            if (!is_dir($invoiceDir)) {
                mkdir($invoiceDir, 0777, true);
            }

            $invoicePath = $pdfGenerator->generateAndSaveInvoice($order, $invoiceDir);
            $order->setInvoicePath('/invoices/' . basename($invoicePath));

            // Send invoice email
            $mailerService->sendInvoiceEmail($order);
        }

        $entityManager->flush();

        return new JsonResponse([
            'message' => 'Order status updated successfully',
            'new_status' => $order->getStatus()->value,
            'invoice_path' => $order->getInvoicePath()
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
