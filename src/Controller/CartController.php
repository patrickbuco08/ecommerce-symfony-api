<?php

namespace Bocum\Controller;

use Bocum\Entity\Cart;
use Bocum\Entity\User;
use Bocum\Entity\Product;
use Bocum\Repository\CartRepository;
use Bocum\Service\CartService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Security\Http\Attribute\CurrentUser;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

#[Route('/api/carts')]
class CartController extends AbstractController
{
    public function __construct(private CartService $cartService) {}

    #[Route('', name: 'get_cart', methods: ['GET'])]
    public function getCart(#[CurrentUser] ?User $user): JsonResponse
    {
        if (!$user) {
            return new JsonResponse(['error' => 'Unauthorized'], JsonResponse::HTTP_UNAUTHORIZED);
        }

        $cartItems = $user->getCarts();

        return $this->json($cartItems, 200, [], ['groups' => 'cart:read']);
    }

    #[Route('', name: 'add_to_cart', methods: ['POST'])]
    public function addToCart(#[CurrentUser] ?User $user, Request $request): JsonResponse
    {
        if (!$user) {
            return new JsonResponse(['error' => 'Unauthorized'], JsonResponse::HTTP_UNAUTHORIZED);
        }

        $data = json_decode($request->getContent(), true);
        if (!isset($data['productId'], $data['quantity'])) {
            return new JsonResponse(['error' => 'Missing fields'], JsonResponse::HTTP_BAD_REQUEST);
        }

        return $this->cartService->addToCart($user, $data['productId'], $data['quantity']);
    }

    #[Route('/{id}', name: 'remove_from_cart', methods: ['DELETE'])]
    public function removeFromCart(#[CurrentUser] ?User $user, int $id): JsonResponse
    {
        if (!$user) {
            return new JsonResponse(['error' => 'Unauthorized'], JsonResponse::HTTP_UNAUTHORIZED);
        }

        return $this->cartService->removeFromCart($user, $id);
    }
}
