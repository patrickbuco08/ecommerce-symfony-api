<?php

namespace Bocum\Transformer;

use Bocum\Dto\UserDto;
use Bocum\Entity\User;

class UserTransformer
{
    public function transform(User $user): UserDto
    {
        return new UserDto(
            $user->getId(),
            $user->getFirstName(),
            $user->getLastName(),
            $user->getPhone(),
            $user->getEmail(),
            $user->getRoles(),
            $user->getImage(),
        );
    }

    public function transformCollection(array $users): array
    {
        return array_map(fn(User $user) => $this->transform($user), $users);
    }
}
