<?php

namespace Bocum\Transformer;

use Bocum\Entity\Store;
use Bocum\Dto\StoreDto;
use Bocum\Transformer\UserTransformer;

class StoreTransformer
{
    public function __construct(private UserTransformer $userTransformer) {}

    public function transform(Store $store): StoreDto
    {
        return new StoreDto(
            $store->getId(),
            $store->getSlug(),
            $store->getName(),
            $store->getDescription(),
            $store->getLogo(),
            $store->isActive(),
            $this->userTransformer->transform($store->getOwner())
        );
    }

    /**
     * @param Store[] $stores
     * @return StoreDto[]
     */
    public function transformCollection(array $stores): array
    {
        return array_map(fn(Store $store) => $this->transform($store), $stores);
    }
}
