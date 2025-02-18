<?php

namespace Bocum\Controller;

use Bocum\Entity\Order;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

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

    #[Route('/api/admin/dashboard/stats', name: 'admin_dashboard_stats', methods: ['GET'])]
    #[IsGranted('ROLE_ADMIN')]
    public function getDashboardStats(EntityManagerInterface $entityManager): JsonResponse
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

        // Orders per month for the last 12 months
        $orders = $entityManager->createQuery(
            "SELECT o.createdAt, COUNT(o.id) as count
             FROM App\Entity\Order o
             WHERE o.createdAt >= :lastYear
             GROUP BY o.createdAt
             ORDER BY o.createdAt ASC"
        )->setParameter('lastYear', (new \DateTime('-12 months'))->format('Y-m-d'))
            ->getResult();

        $monthlyOrders = [];

        foreach ($orders as $order) {
            $month = $order['createdAt']->format('m');

            $monthlyOrders[] = [
                'month' => (int) $month,
                'count' => (int) $order['count'],
            ];
        }

        return new JsonResponse([
            'totalOrders' => $totalOrders,
            'totalRevenue' => (float) $totalRevenue,
            'orderByStatus' => $statusData,
            'monthlyOrders' => $monthlyOrders
        ]);
    }

    #[Route('/api/admin/orders/export', name: 'export_orders', methods: ['GET'])]
    #[IsGranted('ROLE_ADMIN')]
    public function exportOrders(
        Request $request,
        EntityManagerInterface $entityManager
    ): Response {
        $format = $request->query->get('format', 'json'); // Default to JSON
        $startDate = $request->query->get('start_date');
        $endDate = $request->query->get('end_date');
        $userEmail = $request->query->get('user_email');
        $productName = $request->query->get('product_name');

        $queryBuilder = $entityManager->getRepository(Order::class)->createQueryBuilder('o')
            ->leftJoin('o.items', 'oi')
            ->leftJoin('oi.product', 'p')
            ->leftJoin('o.user', 'u');

        if ($startDate) {
            $queryBuilder->andWhere('o.createdAt >= :startDate')
                ->setParameter('startDate', new \DateTimeImmutable($startDate));
        }

        if ($endDate) {
            $queryBuilder->andWhere('o.createdAt <= :endDate')
                ->setParameter('endDate', new \DateTimeImmutable($endDate));
        }

        if ($userEmail) {
            $queryBuilder->andWhere('u.email = :userEmail')
                ->setParameter('userEmail', $userEmail);
        }

        if ($productName) {
            $queryBuilder->andWhere('p.name LIKE :productName')
                ->setParameter('productName', '%' . $productName . '%');
        }

        $orders = $queryBuilder->getQuery()->getResult();

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

        return ($format === 'csv') ? $this->exportCSV($data) : new JsonResponse($data);
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
