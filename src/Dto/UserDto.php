<?php

namespace Bocum\Dto;

class UserDto
{
    public function __construct(
        public int $id,
        public string $firstName,
        public string $lastName,
        public string $phone,
        public string $email,
        public array $roles,
        public ?string $image = null,
    ) {}
}
