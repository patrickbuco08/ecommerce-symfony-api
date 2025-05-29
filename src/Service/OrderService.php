<?php

namespace Bocum\Service;

use Bocum\Entity\User;
use Bocum\Entity\Order;
use Bocum\Enum\OrderStatus;
use Bocum\Factory\OrderFactory;
use Bocum\Dto\Request\OrderData;
use Bocum\Service\MailerService;
use Bocum\Service\PaginationService;
use Bocum\Transformer\OrderTransformer;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;

class OrderService
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private MailerService $mailerService,
        private ParameterBagInterface $params,
        private PdfGenerator $pdfGenerator,
        private OrderFactory $orderFactory,
        private OrderTransformer $orderTransformer,
        private PaginationService $paginationService,
    ) {}

    public function create(UserInterface $user, array $data): array
    {
        if (!isset($data['items']) || !is_array($data['items'])) {
            return ['error' => 'Invalid order data'];
        }

        $order = $this->orderFactory->createOrder(OrderData::fromArray($data, $user));
        $this->entityManager->persist($order);
        $this->entityManager->flush();

        return ['message' => 'Order placed successfully', 'order_id' => $order->getId()];
    }

    public function getUserOrders(User $user): array
    {
        return $this->orderTransformer->transformCollection($user->getOrders()->toArray());
    }

    public function updateOrderStatus(Order $order, $data): Order
    {
        $newStatus = OrderStatus::from($data['status']);

        $order->setStatus($newStatus);

        // If order is marked as completed, generate an invoice
        if ($newStatus === OrderStatus::COMPLETED && !$order->getInvoicePath()) {
            $invoiceDir = $this->params->get('kernel.project_dir') . '/public/invoices';
            if (!is_dir($invoiceDir)) {
                mkdir($invoiceDir, 0777, true);
            }

            $invoicePath = $this->pdfGenerator->generateAndSaveInvoice($order, $invoiceDir);
            $order->setInvoicePath('/invoices/' . basename($invoicePath));

            $this->mailerService->sendInvoiceEmail($order);
        }

        $this->entityManager->flush();

        return $order;
    }

    public function orderToArray(Order $order)
    {
        return $this->orderTransformer->transform($order);
    }

    public function paginateUserOrdersByStatus(User $user, string $status, Request $request)
    {
        $qb = $this->entityManager->getRepository(Order::class)->getUserOrdersByStatusQueryBuilder($user, $status);
        return $this->paginationService->paginate($qb, $request);
    }
}
