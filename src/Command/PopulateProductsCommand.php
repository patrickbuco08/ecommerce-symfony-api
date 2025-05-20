<?php

namespace Bocum\Command;

use Bocum\Entity\Product;
use Bocum\Entity\Category;
use Bocum\Entity\ProductImage;
use Bocum\Entity\Tag;
use Bocum\Entity\User;
use Bocum\Command\UserData;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Bocum\Command\CategoryData;

#[AsCommand(
    name: 'app:populate-products',
    description: 'Populates the products table with default coffee shop products, owners, and their relationships.'
)]
class PopulateProductsCommand extends Command
{
    public function __construct(private EntityManagerInterface $entityManager)
    {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $categoriesRepo = $this->entityManager->getRepository(Category::class);
        $categories = [];
        foreach (CategoryData::CATEGORIES as $cat) {
            $categories[$cat['slug']] = $categoriesRepo->findOneBy(['slug' => $cat['slug']]);
        }

        $products = [
            [
                'title' => 'Classic Espresso',
                'slug' => 'classic-espresso',
                'description' => 'Rich and bold espresso shot.',
                'price' => 2.50,
                'rating' => 4.8,
                'stock' => 100,
                'category' => 'espresso-beverages',
                'tags' => ['hot', 'strong'],
                'images' => [
                    'https://images.unsplash.com/photo-1511920170033-f8396924c348',
                    'https://images.pexels.com/photos/302902/pexels-photo-302902.jpeg'
                ],
            ],
            [
                'title' => 'House Brewed Coffee',
                'slug' => 'house-brewed-coffee',
                'description' => 'Smooth, aromatic brewed coffee.',
                'price' => 2.00,
                'rating' => 4.5,
                'stock' => 120,
                'category' => 'brewed-coffee',
                'tags' => ['hot', 'classic'],
                'images' => [
                    'https://images.unsplash.com/photo-1506744038136-46273834b3fb'
                ],
            ],
            [
                'title' => 'Chai Tea Latte',
                'slug' => 'chai-tea-latte',
                'description' => 'Spiced black tea with steamed milk.',
                'price' => 3.00,
                'rating' => 4.7,
                'stock' => 80,
                'category' => 'tea-infusions',
                'tags' => ['hot', 'spiced'],
                'images' => [
                    'https://images.pexels.com/photos/461382/pexels-photo-461382.jpeg'
                ],
            ],
            [
                'title' => 'Blueberry Muffin',
                'slug' => 'blueberry-muffin',
                'description' => 'Freshly baked muffin with blueberries.',
                'price' => 2.75,
                'rating' => 4.6,
                'stock' => 60,
                'category' => 'pastries-snacks',
                'tags' => ['sweet', 'baked'],
                'images' => [
                    'https://images.unsplash.com/photo-1504674900247-0877df9cc836'
                ],
            ],
            [
                'title' => 'Iced Caramel Macchiato',
                'slug' => 'iced-caramel-macchiato',
                'description' => 'Chilled espresso with caramel and milk.',
                'price' => 3.50,
                'rating' => 4.9,
                'stock' => 90,
                'category' => 'cold-drinks',
                'tags' => ['cold', 'sweet'],
                'images' => [
                    'https://images.unsplash.com/photo-1464983953574-0892a716854b',
                    'https://images.pexels.com/photos/2101187/pexels-photo-2101187.jpeg'
                ],
            ],
            [
                'title' => 'Vanilla Latte',
                'slug' => 'vanilla-latte',
                'description' => 'Espresso with steamed milk and vanilla syrup.',
                'price' => 3.25,
                'rating' => 4.6,
                'stock' => 95,
                'category' => 'espresso-beverages',
                'tags' => ['hot', 'sweet'],
                'images' => [
                    'https://images.pexels.com/photos/302896/pexels-photo-302896.jpeg'
                ],
            ],
            [
                'title' => 'Americano',
                'slug' => 'americano',
                'description' => 'Espresso diluted with hot water.',
                'price' => 2.20,
                'rating' => 4.4,
                'stock' => 110,
                'category' => 'espresso-beverages',
                'tags' => ['hot', 'classic'],
                'images' => [
                    'https://images.unsplash.com/photo-1519125323398-675f0ddb6308'
                ],
            ],
            [
                'title' => 'Cappuccino',
                'slug' => 'cappuccino',
                'description' => 'Espresso with steamed milk and foam.',
                'price' => 3.00,
                'rating' => 4.8,
                'stock' => 105,
                'category' => 'espresso-beverages',
                'tags' => ['hot', 'frothy'],
                'images' => [
                    'https://images.pexels.com/photos/34085/pexels-photo.jpg'
                ],
            ],
            [
                'title' => 'Mocha',
                'slug' => 'mocha',
                'description' => 'Espresso with chocolate and steamed milk.',
                'price' => 3.50,
                'rating' => 4.7,
                'stock' => 85,
                'category' => 'espresso-beverages',
                'tags' => ['hot', 'chocolate'],
                'images' => [
                    'https://images.pexels.com/photos/302899/pexels-photo-302899.jpeg'
                ],
            ],
            [
                'title' => 'Flat White',
                'slug' => 'flat-white',
                'description' => 'Espresso with microfoam milk.',
                'price' => 3.10,
                'rating' => 4.5,
                'stock' => 75,
                'category' => 'espresso-beverages',
                'tags' => ['hot', 'smooth'],
                'images' => [
                    'https://images.unsplash.com/photo-1454023492550-5696f8ff10e1'
                ],
            ],
            [
                'title' => 'French Press Coffee',
                'slug' => 'french-press-coffee',
                'description' => 'Full-bodied coffee brewed in a French press.',
                'price' => 2.80,
                'rating' => 4.3,
                'stock' => 70,
                'category' => 'brewed-coffee',
                'tags' => ['hot', 'rich'],
                'images' => [
                    'https://images.pexels.com/photos/585750/pexels-photo-585750.jpeg'
                ],
            ],
            [
                'title' => 'Cold Brew',
                'slug' => 'cold-brew',
                'description' => 'Coffee brewed cold for a smooth finish.',
                'price' => 3.20,
                'rating' => 4.7,
                'stock' => 80,
                'category' => 'cold-drinks',
                'tags' => ['cold', 'smooth'],
                'images' => [
                    'https://images.pexels.com/photos/374885/pexels-photo-374885.jpeg'
                ],
            ],
            [
                'title' => 'Matcha Latte',
                'slug' => 'matcha-latte',
                'description' => 'Japanese green tea with steamed milk.',
                'price' => 3.75,
                'rating' => 4.6,
                'stock' => 60,
                'category' => 'tea-infusions',
                'tags' => ['hot', 'green'],
                'images' => [
                    'https://images.unsplash.com/photo-1464306076886-debca5e8a6b0'
                ],
            ],
            [
                'title' => 'Herbal Infusion',
                'slug' => 'herbal-infusion',
                'description' => 'Caffeine-free herbal tea blend.',
                'price' => 2.95,
                'rating' => 4.2,
                'stock' => 50,
                'category' => 'tea-infusions',
                'tags' => ['hot', 'herbal'],
                'images' => [
                    'https://images.pexels.com/photos/1417945/pexels-photo-1417945.jpeg'
                ],
            ],
            [
                'title' => 'Chocolate Croissant',
                'slug' => 'chocolate-croissant',
                'description' => 'Flaky croissant filled with chocolate.',
                'price' => 2.90,
                'rating' => 4.8,
                'stock' => 55,
                'category' => 'pastries-snacks',
                'tags' => ['sweet', 'baked'],
                'images' => [
                    'https://images.pexels.com/photos/461382/pexels-photo-461382.jpeg'
                ],
            ],
            [
                'title' => 'Banana Bread',
                'slug' => 'banana-bread',
                'description' => 'Moist banana bread with walnuts.',
                'price' => 2.60,
                'rating' => 4.5,
                'stock' => 65,
                'category' => 'pastries-snacks',
                'tags' => ['sweet', 'nutty'],
                'images' => [
                    'https://images.pexels.com/photos/461382/pexels-photo-461382.jpeg'
                ],
            ],
            [
                'title' => 'Strawberry Danish',
                'slug' => 'strawberry-danish',
                'description' => 'Danish pastry with strawberry filling.',
                'price' => 2.80,
                'rating' => 4.4,
                'stock' => 45,
                'category' => 'pastries-snacks',
                'tags' => ['sweet', 'fruity'],
                'images' => [
                    'https://images.pexels.com/photos/461382/pexels-photo-461382.jpeg'
                ],
            ],
            [
                'title' => 'Iced Mocha',
                'slug' => 'iced-mocha',
                'description' => 'Chilled chocolate and espresso drink.',
                'price' => 3.60,
                'rating' => 4.7,
                'stock' => 77,
                'category' => 'cold-drinks',
                'tags' => ['cold', 'chocolate'],
                'images' => [
                    'https://images.unsplash.com/photo-1464983953574-0892a716854b'
                ],
            ],
            [
                'title' => 'Lemon Iced Tea',
                'slug' => 'lemon-iced-tea',
                'description' => 'Refreshing iced tea with lemon.',
                'price' => 2.70,
                'rating' => 4.3,
                'stock' => 85,
                'category' => 'cold-drinks',
                'tags' => ['cold', 'citrus'],
                'images' => [
                    'https://images.unsplash.com/photo-1506744038136-46273834b3fb'
                ],
            ],
            [
                'title' => 'Spinach Quiche',
                'slug' => 'spinach-quiche',
                'description' => 'Savory quiche with spinach and cheese.',
                'price' => 3.20,
                'rating' => 4.4,
                'stock' => 38,
                'category' => 'pastries-snacks',
                'tags' => ['savory', 'baked'],
                'images' => [
                    'https://images.pexels.com/photos/461382/pexels-photo-461382.jpeg'
                ],
            ],
            [
                'title' => 'Coconut Macaroon',
                'slug' => 'coconut-macaroon',
                'description' => 'Sweet coconut macaroon cookie.',
                'price' => 2.30,
                'rating' => 4.1,
                'stock' => 42,
                'category' => 'pastries-snacks',
                'tags' => ['sweet', 'coconut'],
                'images' => [
                    'https://images.pexels.com/photos/461382/pexels-photo-461382.jpeg'
                ],
            ],
            [
                'title' => 'Raspberry Scone',
                'slug' => 'raspberry-scone',
                'description' => 'Buttery scone with raspberries.',
                'price' => 2.85,
                'rating' => 4.2,
                'stock' => 47,
                'category' => 'pastries-snacks',
                'tags' => ['sweet', 'fruity'],
                'images' => [
                    'https://images.pexels.com/photos/461382/pexels-photo-461382.jpeg'
                ],
            ],
            [
                'title' => 'Pumpkin Spice Latte',
                'slug' => 'pumpkin-spice-latte',
                'description' => 'Espresso with pumpkin spice and steamed milk.',
                'price' => 3.80,
                'rating' => 4.9,
                'stock' => 67,
                'category' => 'espresso-beverages',
                'tags' => ['hot', 'spiced'],
                'images' => [
                    'https://images.unsplash.com/photo-1519125323398-675f0ddb6308'
                ],
            ],
            [
                'title' => 'Honey Lemon Tea',
                'slug' => 'honey-lemon-tea',
                'description' => 'Hot tea with honey and lemon.',
                'price' => 2.90,
                'rating' => 4.6,
                'stock' => 53,
                'category' => 'tea-infusions',
                'tags' => ['hot', 'citrus'],
                'images' => [
                    'https://images.unsplash.com/photo-1464306076886-debca5e8a6b0'
                ],
            ],
            [
                'title' => 'Almond Biscotti',
                'slug' => 'almond-biscotti',
                'description' => 'Crunchy almond biscotti cookie.',
                'price' => 2.20,
                'rating' => 4.2,
                'stock' => 40,
                'category' => 'pastries-snacks',
                'tags' => ['sweet', 'nutty'],
                'images' => [
                    'https://images.pexels.com/photos/461382/pexels-photo-461382.jpeg'
                ],
            ],
            [
                'title' => 'Green Tea',
                'slug' => 'green-tea',
                'description' => 'Classic antioxidant-rich green tea.',
                'price' => 2.50,
                'rating' => 4.3,
                'stock' => 70,
                'category' => 'tea-infusions',
                'tags' => ['hot', 'green'],
                'images' => [
                    'https://images.unsplash.com/photo-1464306076886-debca5e8a6b0'
                ],
            ],
            [
                'title' => 'Double Chocolate Brownie',
                'slug' => 'double-chocolate-brownie',
                'description' => 'Rich, fudgy brownie with chocolate chips.',
                'price' => 2.95,
                'rating' => 4.8,
                'stock' => 41,
                'category' => 'pastries-snacks',
                'tags' => ['sweet', 'chocolate'],
                'images' => [
                    'https://images.pexels.com/photos/461382/pexels-photo-461382.jpeg'
                ],
            ],
        ];

        // Fetch users from DB by email
        $userRepo = $this->entityManager->getRepository(User::class);
        $userEmails = array_column(UserData::USERS, 'email');
        $users = [];
        foreach ($userEmails as $email) {
            $user = $userRepo->findOneBy(['email' => $email]);
            if ($user) {
                $users[] = $user;
            }
        }
        if (empty($users)) {
            $output->writeln('<error>No users found. Please run app:populate-users first.</error>');
            return Command::FAILURE;
        }
        $userCount = count($users);
        $prodIndex = 0;
        foreach ($products as $prodData) {
            $existing = $this->entityManager->getRepository(Product::class)->findOneBy(['slug' => $prodData['slug']]);
            if ($existing) {
                $output->writeln("Product '{$prodData['title']}' already exists, skipping.");
                $prodIndex++;
                continue;
            }
            $product = new Product();
            $product->setTitle($prodData['title']);
            $product->setSlug($prodData['slug']);
            $product->setDescription($prodData['description']);
            $product->setPrice($prodData['price']);
            $product->setRating($prodData['rating']);
            $product->setStock($prodData['stock']);
            $product->setCategory($categories[$prodData['category']]);
            $product->setCreatedAt(new \DateTimeImmutable());
            // Assign owner
            $owner = $users[$prodIndex % $userCount];
            $product->setUser($owner);

            // Add tags
            foreach ($prodData['tags'] as $tagName) {
                $tag = new Tag();
                $tag->setName($tagName);
                $tag->setProduct($product);
                $product->addTag($tag);
                $this->entityManager->persist($tag);
            }

            // Add images
            foreach ($prodData['images'] as $imgName) {
                $image = new ProductImage();
                $image->setName($imgName);
                $image->setProduct($product);
                $product->addImage($image);
                $this->entityManager->persist($image);
            }

            // Add reviews (1-3 per product, random users)
            $reviewCount = rand(1, 3);
            $reviewerIndexes = array_rand($users, $reviewCount);
            if (!is_array($reviewerIndexes)) {
                $reviewerIndexes = [$reviewerIndexes];
            }
            $sampleComments = [
                'Great taste!',
                'Would buy again.',
                'A bit too strong for me.',
                'Perfect with breakfast.',
                'Highly recommended!',
                'Not my favorite.',
                'Delicious and fresh.',
                'Nice presentation.',
                'Value for money.',
                'Will try other products next time.',
            ];
            foreach ($reviewerIndexes as $reviewerIdx) {
                $review = new \Bocum\Entity\Review();
                $review->setUser($users[$reviewerIdx]);
                $review->setProduct($product);
                $review->setRating(rand(3, 5));
                $review->setComment($sampleComments[array_rand($sampleComments)]);
                $this->entityManager->persist($review);
                $product->addReview($review);
            }

            $this->entityManager->persist($product);
            $output->writeln("Added product: {$prodData['title']} (Owner: {$owner->getEmail()})");
            $prodIndex++;
        }
        $this->entityManager->flush();
        $output->writeln('Products populated successfully.');
        return Command::SUCCESS;
    }
}
