<?php

namespace Bocum\Controller;

use Bocum\Entity\Product;
use Bocum\Service\ProductService;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\RateLimiter\RateLimiterFactory;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpKernel\Exception\TooManyRequestsHttpException;

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

        if (!isset($data['title'], $data['price'], $data['stock'])) {
            return new JsonResponse(['error' => 'Missing required fields']);
        }

        $result = $this->productService->create($data);

        return new JsonResponse($result, isset($result['error']) ? JsonResponse::HTTP_BAD_REQUEST : JsonResponse::HTTP_CREATED);
    }

    #[Route('', name: 'get_products', methods: ['GET'])]
    public function getProducts(): JsonResponse
    {
        return new JsonResponse($this->productService->getAllProducts(), JsonResponse::HTTP_OK);
    }

    #[Route('/{id}', name: 'get_product', methods: ['GET'])]
    public function getProduct(Request $request, $id, RateLimiterFactory $loginLimiter): JsonResponse
    {
        $limiter = $loginLimiter->create($request->getClientIp());

        if (!$limiter->consume(1)->isAccepted()) {
            throw new TooManyRequestsHttpException('Too many login attempts, please try again later.');
        }

        return new JsonResponse($this->productService->getProductById($id), JsonResponse::HTTP_OK);
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
