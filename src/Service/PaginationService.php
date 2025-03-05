<?php

namespace Bocum\Service;

use Pagerfanta\Pagerfanta;
use Symfony\Component\HttpFoundation\Request;
use Pagerfanta\Doctrine\ORM\QueryAdapter;

class PaginationService
{
    public function paginate($queryBuilder, Request $request, int $maxPerPage = 10): array
    {
        $adapter = new QueryAdapter($queryBuilder);
        $pagerfanta = new Pagerfanta($adapter);

        $page = max(1, (int) $request->query->get('page', 1));
        $pagerfanta->setMaxPerPage($maxPerPage);
        $pagerfanta->setCurrentPage($page);

        return [
            'page' => $page,
            'total_pages' => $pagerfanta->getNbPages(),
            'total_results' => $pagerfanta->getNbResults(),
            'results' => iterator_to_array($pagerfanta->getCurrentPageResults())
        ];
    }
}
