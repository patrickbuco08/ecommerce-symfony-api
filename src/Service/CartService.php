<?php

namespace Bocum\Service;

use Bocum\Entity\Cart;
use Bocum\Entity\User;
use Bocum\Entity\Product;
use Bocum\Repository\CartRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;

class CartService
{
    public function __construct(
        private EntityManagerInterface $em,
        private CartRepository $cartRepository
    ) {}

    public function addToCart(User $user, int $productId, int $quantity): JsonResponse
    {
        $product = $this->em->getRepository(Product::class)->find($productId);

        if (!$product) {
            return new JsonResponse(['error' => 'Product not found'], JsonResponse::HTTP_NOT_FOUND);
        }

        // todo: create cartfactory
        $cart = $this->cartRepository->findOneBy([
            'user' => $user,
            'product' => $product
        ]);

        if ($cart) {
            $cart->setQuantity($cart->getQuantity() + $quantity);
        } else {
            $cart = new Cart();
            $cart->setUser($user);
            $cart->setProduct($product);
            $cart->setQuantity($quantity);
            $this->em->persist($cart);
        }

        $this->em->flush();
        return new JsonResponse(['message' => 'Cart updated successfully'], JsonResponse::HTTP_OK);
    }

    public function removeFromCart(User $user, int $cartId)
    {
        $cartItem = $this->cartRepository->find($cartId);

        if (!$cartItem) {
            return new JsonResponse(['error' => 'Cart item not found'], JsonResponse::HTTP_NOT_FOUND);
        }

        if ($cartItem->getUser()->getId() !== $user->getId()) {
            return new JsonResponse(['error' => 'Forbidden: You do not own this cart item'], JsonResponse::HTTP_FORBIDDEN);
        }

        $this->em->remove($cartItem);
        $this->em->flush();

        return new JsonResponse(null, JsonResponse::HTTP_NO_CONTENT);
    }
}
