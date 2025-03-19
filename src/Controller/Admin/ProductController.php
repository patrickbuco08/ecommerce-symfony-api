<?php

namespace Bocum\Controller\Admin;

use Bocum\Entity\Product;
use Bocum\Service\ProductService;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

#[Route('/api/admin/products')]
#[IsGranted('ROLE_ADMIN')]
class ProductController extends AbstractController
{
    public function __construct(private ProductService $productService) {}

    #[Route('', name: 'create_product', methods: ['POST'])]
    public function createProduct(
        Request $request,
    ): JsonResponse {
        $data = json_decode($request->getContent(), true);

        if (!isset($data['title'], $data['price'], $data['stock'])) {
            return new JsonResponse(['error' => 'Missing required fields']);
        }

        $result = $this->productService->create($data);

        return new JsonResponse($result, isset($result['error']) ? JsonResponse::HTTP_BAD_REQUEST : JsonResponse::HTTP_CREATED);
    }

    #[Route('/{id}', name: 'update_product', methods: ['PUT'])]
    public function updateProduct(
        Request $request,
        Product $product,
    ): JsonResponse {
        $data = json_decode($request->getContent(), true);

        $this->productService->update($product, $data);

        return new JsonResponse(['message' => 'Product updated successfully'], JsonResponse::HTTP_OK);
    }

    #[Route('/{id}', name: 'delete_product', methods: ['DELETE'])]
    public function deleteProduct(Product $product): JsonResponse
    {
        $this->productService->destroy($product);

        return new JsonResponse(['message' => 'Product deleted successfully'], JsonResponse::HTTP_OK);
    }
}
