<?php

namespace App\Controller;

use App\Entity\Order;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

class AdminController extends AbstractController
{
    #[Route('/api/admin/dashboard', name: 'admin_dashboard', methods: ['GET'])]
    #[IsGranted('ROLE_ADMIN')]
    public function getOrderStatistics(EntityManagerInterface $entityManager): JsonResponse
    {
        $orderRepo = $entityManager->getRepository(Order::class);

        $totalOrders = $orderRepo->count([]);
        $totalRevenue = $orderRepo->createQueryBuilder('o')
            ->select('SUM(o.total)')
            ->getQuery()
            ->getSingleScalarResult();

        $statusCounts = $orderRepo->createQueryBuilder('o')
            ->select('o.status, COUNT(o.id) as count')
            ->groupBy('o.status')
            ->getQuery()
            ->getResult();

        $statusData = [];
        foreach ($statusCounts as $status) {
            $statusData[$status['status']->value] = (int) $status['count'];
        }

        return new JsonResponse([
            'total_orders' => $totalOrders,
            'total_revenue' => (float) $totalRevenue,
            'orders_by_status' => $statusData
        ]);
    }

    #[Route('/api/admin/orders/export', name: 'export_orders', methods: ['GET'])]
    #[IsGranted('ROLE_ADMIN')]
    public function exportOrders(
        Request $request,
        EntityManagerInterface $entityManager
    ): Response {
        $format = $request->query->get('format', 'json'); // Default to JSON
        $orders = $entityManager->getRepository(Order::class)->findAll();

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
        ], $orders);

        if ($format === 'csv') {
            return $this->exportCSV($data);
        }

        return new JsonResponse($data);
    }

    private function exportCSV(array $data): Response
    {
        $csvContent = "ID,User,Status,Total,Created At,Items\n";

        foreach ($data as $order) {
            $items = array_map(fn($item) => "{$item['product']} ({$item['quantity']} x {$item['price']})", $order['items']);
            $itemsString = implode('; ', $items);

            $csvContent .= "{$order['id']},{$order['user']},{$order['status']},{$order['total']},{$order['createdAt']},\"$itemsString\"\n";
        }

        $response = new Response($csvContent);
        $response->headers->set('Content-Type', 'text/csv');
        $response->headers->set('Content-Disposition', 'attachment; filename="orders.csv"');

        return $response;
    }
}
