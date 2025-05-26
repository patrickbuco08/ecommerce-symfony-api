<?php

namespace Bocum\Service;

use Pagerfanta\Pagerfanta;
use Symfony\Component\HttpFoundation\Request;
use Pagerfanta\Doctrine\ORM\QueryAdapter;

use Bocum\Dto\PaginationResult;

class PaginationService
{
    public function paginate($queryBuilder, Request $request, int $maxPerPage = 10): PaginationResult
    {
        $adapter = new QueryAdapter($queryBuilder);
        $pagerfanta = new Pagerfanta($adapter);

        $page = max(1, (int) $request->query->get('page', 1));
        $pagerfanta->setMaxPerPage($maxPerPage);
        $pagerfanta->setCurrentPage($page);

        return new PaginationResult(
            $page,
            $pagerfanta->getNbPages(),
            $pagerfanta->getNbResults(),
            iterator_to_array($pagerfanta->getCurrentPageResults())
        );
    }
}

