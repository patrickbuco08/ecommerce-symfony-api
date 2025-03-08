<?php

namespace Bocum\Dto;

class OrderDto
{
    public function __construct(
        public int $id,
        public string $user,
        public string $status,
        public int $total,
        public string $createdAt,
        public array $items,
    ) {}
}
