<?php

namespace Bocum\Service;

use Bocum\Entity\Product;
use Bocum\Entity\Category;
use Bocum\Dto\PaginationResult;
use Bocum\Factory\ProductFactory;
use Bocum\Service\PaginationService;
use Doctrine\ORM\EntityManagerInterface;
use Bocum\Transformer\ProductTransformer;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Contracts\Cache\CacheInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Validator\Exception\ValidatorException;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class ProductService
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private ValidatorInterface $validator,
        private ProductTransformer $productTransformer,
        private ProductFactory $productFactory,
        private CacheInterface $cache,
        private Security $security,
        private PaginationService $paginationService
    ) {}

    public function searchProducts(string $query, Request $request): PaginationResult
    {
        $qb = $this->entityManager->getRepository(Product::class)->searchProductQueryBuilder($query);
        $pagination = $this->paginationService->paginate($qb, $request);
        $pagination->results = $this->productTransformer->transformCollection($pagination->results);

        return $pagination;
    }

    public function getProductById(int $id)
    {
        return $this->cache->get("product_{$id}", function () use ($id) {
            $product = $this->entityManager->getRepository(Product::class)->find($id);

            if (!$product) {
                throw new NotFoundHttpException('Product not found');
            }

            return $this->productToArray($product);
        });
    }

    public function getProductBySlug(string $slug)
    {
        return $this->cache->get("product_by_slug_{$slug}", function () use ($slug) {
            $product = $this->entityManager->getRepository(Product::class)->findOneBy(['slug' => $slug]);

            if (!$product) {
                throw new NotFoundHttpException('Product not found');
            }

            return $this->productToArray($product);
        });
    }

    public function create(array $data): Product
    {
        $category = $this->entityManager->getRepository(Category::class)->findOneBy(['id' => $data['category_id']]);

        if (!$category) {
            throw new NotFoundHttpException('Category not found');
        }

        $product = $this->productFactory->create(
            $data['title'],
            $category,
            $data['description'] ?? null,
            (float) $data['price'],
            (int) $data['stock'],
            (float) $data['rating'],
            $this->security->getUser()
        );

        $errors = $this->validator->validate($product);
        if (count($errors) > 0) {
            $errorMessages = array_map(fn($e) => $e->getMessage(), iterator_to_array($errors));
            throw new ValidatorException(implode('; ', $errorMessages));
        }

        $this->entityManager->persist($product);
        $this->entityManager->flush();

        return $product;
    }

    public function update(Product $product, $data)
    {
        if (isset($data['name'])) {
            $product->setTitle($data['name']);
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
        $errors = $this->validator->validate($product);
        if (count($errors) > 0) {
            $errorMessages = [];
            foreach ($errors as $error) {
                $errorMessages[] = $error->getMessage();
            }
            return new JsonResponse(['errors' => $errorMessages], JsonResponse::HTTP_BAD_REQUEST);
        }

        $this->entityManager->flush();
    }

    public function destroy(Product $product)
    {
        $this->entityManager->remove($product);
        $this->entityManager->flush();
    }

    public function getAllProducts(Request $request): PaginationResult
    {
        $qb = $this->entityManager->getRepository(Product::class)->getAllProductsQueryBuilder();
        $pagination = $this->paginationService->paginate($qb, $request);
        $pagination->results = $this->productTransformer->transformCollection($pagination->results);

        return $pagination;
    }

    public function productToArray(Product $product)
    {
        return $this->productTransformer->transform($product);
    }
}
