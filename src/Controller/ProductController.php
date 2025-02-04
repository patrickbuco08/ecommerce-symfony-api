<?php

namespace App\Controller;

use App\Entity\Product;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\Validator\Validator\ValidatorInterface;

#[Route('/api/products')]
class ProductController extends AbstractController
{
    #[Route('', name: 'create_product', methods: ['POST'])]
    #[IsGranted('ROLE_ADMIN')]
    public function createProduct(
        Request $request,
        EntityManagerInterface $entityManager,
        ValidatorInterface $validator
    ): JsonResponse {
        $data = json_decode($request->getContent(), true);

        if (!isset($data['name'], $data['price'], $data['stock'])) {
            return new JsonResponse(['error' => 'Missing required fields'], JsonResponse::HTTP_BAD_REQUEST);
        }

        $product = new Product();
        $product->setName($data['name']);
        $product->setDescription($data['description'] ?? null);
        $product->setPrice((float) $data['price']);
        $product->setStock((int) $data['stock']);

        // Validate the product
        $errors = $validator->validate($product);
        if (count($errors) > 0) {
            $errorMessages = [];
            foreach ($errors as $error) {
                $errorMessages[] = $error->getMessage();
            }
            return new JsonResponse(['errors' => $errorMessages], JsonResponse::HTTP_BAD_REQUEST);
        }

        $entityManager->persist($product);
        $entityManager->flush();

        return new JsonResponse(['message' => 'Product created successfully'], JsonResponse::HTTP_CREATED);
    }

    #[Route('', name: 'get_products', methods: ['GET'])]
    public function getProducts(EntityManagerInterface $entityManager): JsonResponse
    {
        $products = $entityManager->getRepository(Product::class)->findAll();
        $data = [];

        foreach ($products as $product) {
            $data[] = [
                'id' => $product->getId(),
                'name' => $product->getName(),
                'description' => $product->getDescription(),
                'price' => $product->getPrice(),
                'stock' => $product->getStock(),
                'createdAt' => $product->getCreatedAt()->format('Y-m-d H:i:s'),
            ];
        }

        return new JsonResponse($data, JsonResponse::HTTP_OK);
    }

    #[Route('/{id}', name: 'get_product', methods: ['GET'])]
    public function getProduct(Product $product): JsonResponse
    {
        return new JsonResponse([
            'id' => $product->getId(),
            'name' => $product->getName(),
            'description' => $product->getDescription(),
            'price' => $product->getPrice(),
            'stock' => $product->getStock(),
            'createdAt' => $product->getCreatedAt()->format('Y-m-d H:i:s'),
        ], JsonResponse::HTTP_OK);
    }

    #[Route('/{id}', name: 'update_product', methods: ['PUT'])]
    #[IsGranted('ROLE_ADMIN')]
    public function updateProduct(
        Request $request,
        Product $product,
        EntityManagerInterface $entityManager,
        ValidatorInterface $validator
    ): JsonResponse {
        $data = json_decode($request->getContent(), true);

        if (isset($data['name'])) {
            $product->setName($data['name']);
        }
        if (isset($data['description'])) {
            $product->setDescription($data['description']);
        }
        if (isset($data['price'])) {
            $product->setPrice((float) $data['price']);
        }
        if (isset($data['stock'])) {
            $product->setStock((int) $data['stock']);
        }

        // Validate the updated product
        $errors = $validator->validate($product);
        if (count($errors) > 0) {
            $errorMessages = [];
            foreach ($errors as $error) {
                $errorMessages[] = $error->getMessage();
            }
            return new JsonResponse(['errors' => $errorMessages], JsonResponse::HTTP_BAD_REQUEST);
        }

        $entityManager->flush();

        return new JsonResponse(['message' => 'Product updated successfully'], JsonResponse::HTTP_OK);
    }

    #[Route('/{id}', name: 'delete_product', methods: ['DELETE'])]
    #[IsGranted('ROLE_ADMIN')]
    public function deleteProduct(Product $product, EntityManagerInterface $entityManager): JsonResponse
    {
        $entityManager->remove($product);
        $entityManager->flush();

        return new JsonResponse(['message' => 'Product deleted successfully'], JsonResponse::HTTP_OK);
    }
}
