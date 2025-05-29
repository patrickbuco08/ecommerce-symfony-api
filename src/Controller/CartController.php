<?php

namespace Bocum\Controller;

use Bocum\Entity\User;
use Bocum\Service\CartService;
use Bocum\Transformer\CartTransformer;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Security\Http\Attribute\CurrentUser;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

#[Route('/api/carts')]
class CartController extends AbstractController
{
    public function __construct(
        private CartService $cartService,
        private CartTransformer $cartTransformer
    ) {}

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

    #[Route('/{id}', name: 'update_cart_item', methods: ['PUT'])]
    public function updateCartItem(#[CurrentUser] ?User $user, int $id, Request $request): JsonResponse
    {
        if (!$user) {
            return new JsonResponse(['error' => 'Unauthorized'], JsonResponse::HTTP_UNAUTHORIZED);
        }

        $data = json_decode($request->getContent(), true);
        if (!isset($data['quantity'])) {
            return new JsonResponse(['error' => 'Missing quantity field'], JsonResponse::HTTP_BAD_REQUEST);
        }

        try {
            $cart = $this->cartService->updateCartItem($user, $id, $data['quantity']);
            $data = $this->cartTransformer->transform($cart);

            return new JsonResponse($data, JsonResponse::HTTP_OK);
        } catch (\Throwable $th) {
            return new JsonResponse(['error' => $th->getMessage()], JsonResponse::HTTP_BAD_REQUEST);
        }
    }
}
