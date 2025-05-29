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
}
