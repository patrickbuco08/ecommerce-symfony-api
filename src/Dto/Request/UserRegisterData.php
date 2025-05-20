<?php

namespace Bocum\Dto\Request;

class UserRegisterData
{
    public function __construct(
        public string $firstName,
        public string $lastName,
        public string $phone,
        public string $email,
        public string $password
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            $data['first_name'] ?? '',
            $data['last_name'] ?? '',
            $data['phone'] ?? '',
            $data['email'] ?? '',
            $data['password'] ?? ''
        );
    }
}
