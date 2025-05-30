<?php

namespace Bocum\Dto;

use Bocum\Dto\UserDto;

class StoreDto
{
    public function __construct(
        public int $id,
        public string $slug,
        public string $name,
        public ?string $description,
        public ?string $logo,
        public bool $active,
        public UserDto $owner
    ) {}
}
