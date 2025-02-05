<?php

namespace App\Controller;

use App\Entity\Order;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
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
}
