<?php

namespace Bocum\Transformer;

use Bocum\Entity\Review;

class ReviewTransformer
{
    public function transform(Review $review): array
    {
        return [
            'id' => $review->getId(),
            'rating' => $review->getRating(),
            'comment' => $review->getComment(),
        ];
    }

    public function transformCollection(array $reviews): array
    {
        return array_map(fn(Review $review) => $this->transform($review), $reviews);
    }
}
