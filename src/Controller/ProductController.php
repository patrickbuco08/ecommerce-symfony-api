<?php

namespace Bocum\Controller;

use Bocum\Entity\Product;
use Bocum\Service\ProductService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/api/products')]
class ProductController extends AbstractController
{
    private ProductService $productService;

    public function __construct(ProductService $productService)
    {
        $this->productService = $productService;
    }

    #[Route('', name: 'create_product', methods: ['POST'])]
    #[IsGranted('ROLE_ADMIN')]
    public function createProduct(
        Request $request,
    ): JsonResponse {
        $data = json_decode($request->getContent(), true);
        $result = $this->productService->create($data);

        return new JsonResponse($result, isset($result['error']) ? JsonResponse::HTTP_BAD_REQUEST : JsonResponse::HTTP_CREATED);
    }

    #[Route('', name: 'get_products', methods: ['GET'])]
    public function getProducts(): JsonResponse
    {
        return new JsonResponse($this->productService->getAllProducts(), JsonResponse::HTTP_OK);
    }

    #[Route('/{id}', name: 'get_product', methods: ['GET'])]
    public function getProduct(Product $product): JsonResponse
    {
        return new JsonResponse($this->productService->productToArray($product), JsonResponse::HTTP_OK);
    }

    #[Route('/{id}', name: 'update_product', methods: ['PUT'])]
    #[IsGranted('ROLE_ADMIN')]
    public function updateProduct(
        Request $request,
        Product $product,
    ): JsonResponse {
        $data = json_decode($request->getContent(), true);

        $this->productService->update($product, $data);

        return new JsonResponse(['message' => 'Product updated successfully'], JsonResponse::HTTP_OK);
    }

    #[Route('/{id}', name: 'delete_product', methods: ['DELETE'])]
    #[IsGranted('ROLE_ADMIN')]
    public function deleteProduct(Product $product): JsonResponse
    {
        $this->productService->destroy($product);

        return new JsonResponse(['message' => 'Product deleted successfully'], JsonResponse::HTTP_OK);
    }
}
