<?php

namespace Bocum\Controller;

use Bocum\Entity\User;
use Bocum\Entity\Order;
use Bocum\Enum\OrderStatus;
use Bocum\Service\Pagination;
use Bocum\Service\OrderService;
use Bocum\Service\PdfGenerator;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\Security\Http\Attribute\CurrentUser;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;

#[Route('/api/orders')]
class OrderController extends AbstractController
{
    public function __construct(
        private OrderService $orderService,
        private EntityManagerInterface $entityManager,
        private PdfGenerator $pdfGenerator,
        private ParameterBagInterface $params,
    ) {}

    #[Route('', name: 'create_order', methods: ['POST'])]
    #[IsGranted('ROLE_USER')]
    public function createOrder(Request $request, #[CurrentUser] UserInterface $user): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        $result = $this->orderService->create($user, $data);

        return new JsonResponse($result, isset($result['error']) ? JsonResponse::HTTP_BAD_REQUEST : JsonResponse::HTTP_CREATED);
    }

    #[Route('', name: 'get_orders', methods: ['GET'])]
    #[IsGranted('ROLE_USER')]
    public function getUserOrders(#[CurrentUser] UserInterface $user): JsonResponse
    {
        return new JsonResponse($this->orderService->getUserOrders($user));
    }

    #[Route('/my-orders/status/{status}', name: 'get_user_orders_by_status', methods: ['GET'])]
    #[IsGranted('ROLE_USER')]
    public function getUserOrdersByStatus(string $status, #[CurrentUser] User $user, Request $request): JsonResponse
    {
        if (!OrderStatus::tryFrom($status)) {
            return new JsonResponse(['error' => 'Invalid order status'], JsonResponse::HTTP_BAD_REQUEST);
        }

        $queryBuilder = $this->entityManager->getRepository(Order::class)
            ->createQueryBuilder('o')
            ->where('o.user = :user')
            ->andWhere('o.status = :status')
            ->setParameter('user', $user)
            ->setParameter('status', $status)
            ->orderBy('o.createdAt', 'DESC');

        $pagination = Pagination::paginate($queryBuilder, $request);

        return new JsonResponse([
            'page' => $pagination['page'],
            'total_pages' => $pagination['total_pages'],
            'total_orders' => $pagination['total_results'],
            'orders' => array_map(fn($order) => $this->orderService->orderToArray($order), $pagination['results'])
        ]);
    }

    #[Route('/my-orders/{id}/cancel', name: 'cancel_order', methods: ['PUT'])]
    #[IsGranted('ROLE_USER')]
    public function cancelOrder(Order $order, #[CurrentUser] User $user): JsonResponse
    {
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
        $this->entityManager->flush();

        return new JsonResponse([
            'message' => 'Order canceled successfully',
            'new_status' => $order->getStatus()->value
        ]);
    }

    #[Route('/{id}/invoice', name: 'get_order_invoice', methods: ['GET'])]
    #[IsGranted('ROLE_USER')]
    public function getOrderInvoice(Order $order, #[CurrentUser] User $user): Response
    {
        // Ensure the user owns the order or is an admin
        if ($order->getUser() !== $user && !in_array('ROLE_ADMIN', $user->getRoles())) {
            return new JsonResponse(['error' => 'You can only access your own invoices'], JsonResponse::HTTP_FORBIDDEN);
        }

        $pdfContent = $this->pdfGenerator->generateInvoice($order);

        $response = new StreamedResponse(function () use ($pdfContent) {
            echo $pdfContent;
        });

        $response->headers->set('Content-Type', 'application/pdf');
        $response->headers->set('Content-Disposition', 'attachment; filename="invoice-' . $order->getId() . '.pdf"');

        return $response;
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
