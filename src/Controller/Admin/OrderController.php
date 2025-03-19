<?php

namespace Bocum\Controller\Admin;

use Bocum\Entity\Order;
use Bocum\Enum\OrderStatus;
use Bocum\Service\Pagination;
use Bocum\Service\OrderService;
use Bocum\Service\PdfGenerator;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;

#[Route('/api/admin/orders')]
#[IsGranted('ROLE_ADMIN')]
class OrderController extends AbstractController
{
    public function __construct(
        private OrderService $orderService,
        private EntityManagerInterface $entityManager,
        private PdfGenerator $pdfGenerator,
        private ParameterBagInterface $params,
    ) {}

    #[Route('/{id}/status', name: 'update_order_status', methods: ['PUT'])]
    public function updateOrderStatus(Order $order, Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        if (!isset($data['status'])) {
            return new JsonResponse(['error' => 'Status is required'], JsonResponse::HTTP_BAD_REQUEST);
        }

        if (!OrderStatus::tryFrom($data['status'])) {
            return new JsonResponse(['error' => 'Invalid order status'], JsonResponse::HTTP_BAD_REQUEST);
        }

        if (in_array($order->getStatus(), [OrderStatus::COMPLETED, OrderStatus::CANCELLED])) {
            return new JsonResponse(['error' => 'Completed or canceled orders cannot be changed'], JsonResponse::HTTP_BAD_REQUEST);
        }

        $order = $this->orderService->updateOrderStatus($order, $data);

        return new JsonResponse([
            'message' => 'Order status updated successfully',
            'new_status' => $order->getStatus()->value,
            'invoice_path' => $order->getInvoicePath()
        ]);
    }

    #[Route('/status/{status}', name: 'get_orders_by_status', methods: ['GET'])]
    public function getOrdersByStatus(string $status, Request $request): JsonResponse
    {
        if (!OrderStatus::tryFrom($status)) {
            return new JsonResponse(['error' => 'Invalid order status'], JsonResponse::HTTP_BAD_REQUEST);
        }

        $queryBuilder = $this->entityManager->getRepository(Order::class)
            ->createQueryBuilder('o')
            ->where('o.status = :status')
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

    #[Route('/{id}/generate-invoice', name: 'generate_order_invoice', methods: ['POST'])]
    public function generateInvoice(Order $order): JsonResponse
    {
        // Ensure invoice isn't already generated
        if ($order->getInvoicePath()) {
            return new JsonResponse(['error' => 'Invoice already exists'], JsonResponse::HTTP_CONFLICT);
        }

        $invoiceDir = $this->params->get('kernel.project_dir') . '/public/invoices';
        if (!is_dir($invoiceDir)) {
            mkdir($invoiceDir, 0777, true);
        }

        // Generate and save invoice
        $invoicePath = $this->pdfGenerator->generateAndSaveInvoice($order, $invoiceDir);
        $order->setInvoicePath('/invoices/' . basename($invoicePath));

        $this->entityManager->flush();

        return new JsonResponse([
            'message' => 'Invoice generated successfully',
            'invoice_path' => $order->getInvoicePath()
        ]);
    }
}
