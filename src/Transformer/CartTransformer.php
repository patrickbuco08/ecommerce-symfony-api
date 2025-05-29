<?php

namespace Bocum\Transformer;

use Bocum\Entity\Cart;

class CartTransformer
{
    public function transform(Cart $cart): array
    {
        return [
            'id' => $cart->getId(),
            'quantity' => $cart->getQuantity(),
        ];
    }

    public function transformCollection(array $carts): array
    {
        return array_map(fn(Cart $cart) => $this->transform($cart), $carts);
    }
}
