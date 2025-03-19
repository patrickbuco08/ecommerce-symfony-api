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
    public function __construct(private ProductService $productService) {}

    #[Route('', name: 'get_products', methods: ['GET'])]
    public function getProducts(): JsonResponse
    {
        return new JsonResponse($this->productService->getAllProducts(), JsonResponse::HTTP_OK);
    }

    #[Route('/{slug}', name: 'get_product', methods: ['GET'])]
    public function getProduct(Request $request, $slug, RateLimiterFactory $loginLimiter): JsonResponse
    {
        $limiter = $loginLimiter->create($request->getClientIp());

        if (!$limiter->consume(1)->isAccepted()) {
            throw new TooManyRequestsHttpException('Too many login attempts, please try again later.');
        }

        return new JsonResponse($this->productService->getProductBySlug($slug), JsonResponse::HTTP_OK);
    }
}
