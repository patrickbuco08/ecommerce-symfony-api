<?php

namespace Bocum\Dto\Request;

use Bocum\Entity\User;

class OrderData
{
    public function __construct(
        public ?User $user = null,
        public array $items = [],
        public ?string $guestName = null,
        public ?string $guestPhone = null
    ) {}

    public static function fromArray(array $data, ?User $user = null): self
    {
        return new self(
            user: $user,
            items: $data['items'] ?? [],
            guestName: $data['contact']['name'] ?? null,
            guestPhone: $data['contact']['phone'] ?? null
        );
    }
}