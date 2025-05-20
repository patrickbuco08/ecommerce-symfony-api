<?php

namespace Bocum\Transformer;

use Bocum\Entity\Tag;

class TagTransformer
{
    public function transform(Tag $tag): array
    {
        return [
            'id' => $tag->getId(),
            'name' => $tag->getName(),
        ];
    }

    public function transformCollection(array $tags): array
    {
        return array_map(fn(Tag $tag) => $this->transform($tag), $tags);
    }
}
