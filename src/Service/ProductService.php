<?php

namespace Bocum\Service;

use Bocum\Entity\Review;
use Bocum\Entity\Product;
use Bocum\Entity\Category;
use Bocum\Transformer\ProductTransformer;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class ProductService
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private ValidatorInterface $validator,
        private ProductTransformer $productTransformer
    ) {}

    public function create(array $data): array
    {
        if (!isset($data['title'], $data['price'], $data['stock'])) {
            return ['error' => 'Missing required fields'];
        }

        $category = $this->entityManager->getRepository(Category::class)->findOneBy(['id' => $data['category_id']]);

        if (!$category) {
            return ['error' => 'Missing category'];
        }


        $product = new Product();
        $product->setTitle($data['title']);
        $product->setCategory($category);
        $product->setDescription($data['description'] ?? null);
        $product->setPrice((float) $data['price']);
        $product->setStock((int) $data['stock']);
        $product->setRating((float) $data['rating']);



        // Validate the product
        $errors = $this->validator->validate($product);
        if (count($errors) > 0) {
            return ['errors' => array_map(fn($e) => $e->getMessage(), iterator_to_array($errors))];
        }

        $this->entityManager->persist($product);
        $this->entityManager->flush();

        return ['message' => 'Product created successfully', 'id' => $product->getId()];
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

    public function getAllProducts(): array
    {
        $products = $this->entityManager->getRepository(Product::class)->findAll();

        return $this->productTransformer->transformCollection($products);
    }

    public function productToArray(Product $product)
    {
        return $this->productTransformer->transform($product);
    }
}
