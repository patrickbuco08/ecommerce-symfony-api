<?php

namespace Bocum\Event;

use Symfony\Contracts\EventDispatcher\Event;
use Bocum\Entity\User;

class UserRegisteredEvent extends Event
{
    public function __construct(private User $user) {}

    public function getUser(): User
    {
        return $this->user;
    }
}
