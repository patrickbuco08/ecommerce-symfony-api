<?php

namespace Bocum\Tests\Service;

use Bocum\Service\StoreService;
use Bocum\Repository\StoreRepository;
use Bocum\Service\PaginationService;
use Bocum\Dto\PaginationResult;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;
use Doctrine\ORM\QueryBuilder;
use PHPUnit\Framework\MockObject\MockObject;

class StoreServiceTest extends TestCase
{
    public function testGetPaginatedActiveStoresReturnsPaginationResult()
    {
        // Create mocks for dependencies
        /** @var StoreRepository&MockObject $storeRepository */
        $storeRepository = $this->createMock(StoreRepository::class);
        /** @var PaginationService&MockObject $paginationService */
        $paginationService = $this->createMock(PaginationService::class);
        /** @var Request&MockObject $request */
        $request = $this->createMock(Request::class);
        /** @var PaginationResult&MockObject $paginationResult */
        $paginationResult = $this->createMock(PaginationResult::class);

        // Set up the StoreRepository mock to return a QueryBuilder mock
        $queryBuilder = $this->createMock(QueryBuilder::class);
        $storeRepository->method('createQueryBuilder')->willReturn($queryBuilder);

        // Set up the PaginationService mock to return the PaginationResult mock
        $paginationService->expects($this->once())
            ->method('paginate')
            ->with($queryBuilder, $request)
            ->willReturn($paginationResult);

        // Instantiate the service with mocks
        $service = new StoreService($storeRepository, $paginationService);

        // Call the method and assert the result
        $result = $service->getPaginatedActiveStores($request);
        $this->assertSame($paginationResult, $result);
    }
}
