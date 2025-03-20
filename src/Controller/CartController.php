<?php

namespace Bocum\Controller;

use Bocum\Entity\Cart;
use Bocum\Entity\Product;
use Bocum\Entity\User;
use Bocum\Repository\CartRepository;
use Doctrine\ORM\EntityManagerInterface;
use OpenApi\Attributes as OA;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\CurrentUser;
use Symfony\Component\HttpFoundation\Request;

#[Route('/api/carts')]
class CartController extends AbstractController
{
    #[Route('', name: 'get_cart', methods: ['GET'])]
    public function getCart(#[CurrentUser] ?User $user, CartRepository $cartRepository): JsonResponse
    {
        if (!$user) {
            return new JsonResponse(['error' => 'Unauthorized'], JsonResponse::HTTP_UNAUTHORIZED);
        }

        $cartItems = $cartRepository->findByUser($user->getId());
        return $this->json($cartItems, 200, [], ['groups' => 'cart:read']);
    }

    #[Route('', name: 'add_to_cart', methods: ['POST'])]
    public function addToCart(
        #[CurrentUser] ?User $user,
        Request $request,
        EntityManagerInterface $em
    ): JsonResponse {
        if (!$user) {
            return new JsonResponse(['error' => 'Unauthorized'], JsonResponse::HTTP_UNAUTHORIZED);
        }

        $data = json_decode($request->getContent(), true);

        if (!isset($data['productId'], $data['quantity'])) {
            return new JsonResponse(['error' => 'Missing fields'], JsonResponse::HTTP_BAD_REQUEST);
        }

        $product = $em->getRepository(Product::class)->find($data['productId']);

        if (!$product) {
            return new JsonResponse(['error' => 'Product not found'], JsonResponse::HTTP_NOT_FOUND);
        }

        $cart = new Cart();
        $cart->setUser($user);
        $cart->setProduct($product);
        $cart->setQuantity($data['quantity']);

        $em->persist($cart);
        $em->flush();

        return new JsonResponse(['message' => 'Product added to cart'], JsonResponse::HTTP_CREATED);
    }

    #[Route('/{id}', name: 'remove_from_cart', methods: ['DELETE'])]
    public function removeFromCart(int $id, EntityManagerInterface $em, CartRepository $cartRepository): JsonResponse
    {
        $cartItem = $cartRepository->find($id);
        if (!$cartItem) {
            return new JsonResponse(['error' => 'Cart item not found'], JsonResponse::HTTP_NOT_FOUND);
        }

        $em->remove($cartItem);
        $em->flush();

        return new JsonResponse(null, JsonResponse::HTTP_NO_CONTENT);
    }
}
