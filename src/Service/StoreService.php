<?php

namespace Bocum\Service;

use Bocum\Dto\PaginationResult;
use Bocum\Service\PaginationService;
use Bocum\Repository\StoreRepository;
use Symfony\Component\HttpFoundation\Request;

class StoreService
{
    public function __construct(
        private StoreRepository $storeRepository,
        private PaginationService $paginationService
    ) {}

    public function getPaginatedActiveStores(Request $request): PaginationResult
    {
        $qb = $this->storeRepository->createQueryBuilder('s')
            ->andWhere('s.active = :active')
            ->setParameter('active', true)
            ->orderBy('s.name', 'ASC');

        return $this->paginationService->paginate($qb, $request);
    }
}
