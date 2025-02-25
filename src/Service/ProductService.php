<?php

namespace Bocum\Service;

use Bocum\Entity\Category;
use Bocum\Entity\Product;
use Bocum\Entity\Review;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class ProductService
{
    private EntityManagerInterface $entityManager;
    private ValidatorInterface $validator;

    public function __construct(EntityManagerInterface $entityManager, ValidatorInterface $validator)
    {
        $this->entityManager = $entityManager;
        $this->validator = $validator;
    }

    public function createProduct(array $data): array
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

    public function getAllProducts(): array
    {
        $products = $this->entityManager->getRepository(Product::class)->findAll();

        return array_map(fn($product) => $this->productToArray($product), $products);
    }

    public function productToArray(Product $product)
    {
        return [
            'id' => $product->getId(),
            'category' => [
                'id' => $product->getCategory()->getId(),
                'name' => $product->getCategory()->getName(),
                'slug' => $product->getCategory()->getSlug(),
            ],
            'rating' => $product->getRating(),
            'tags' => array_map(fn($item) => [
                'id' => $item->getId(),
                'name' => $item->getName(),
            ], $product->getTags()->toArray()),
            'reviews' => array_map(fn(Review $item) => [
                'id' => $item->getId(),
                'rating' => $item->getRating(),
                'comment' => $item->getComment(),
            ], $product->getReviews()->toArray()),
            'name' => $product->getTitle(),
            'description' => $product->getDescription(),
            'price' => $product->getPrice(),
            'stock' => $product->getStock(),
            'createdAt' => $product->getCreatedAt()->format('Y-m-d H:i:s'),
        ];
    }
}
