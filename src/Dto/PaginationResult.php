<?php

namespace Bocum\Dto;

class PaginationResult
{
    public function __construct(
        public int $page,
        public int $totalPages,
        public int $totalResults,
        public array $results
    ) {}
}
