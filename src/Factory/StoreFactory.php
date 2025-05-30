<?php

namespace Bocum\Factory;

use Bocum\Entity\Store;
use Bocum\Entity\User;

use Bocum\Repository\StoreRepository;

class StoreFactory
{
    private StoreRepository $storeRepository;

    public function __construct(StoreRepository $storeRepository)
    {
        $this->storeRepository = $storeRepository;
    }

    public function create(array $params, User $owner): Store
    {
        $name = $params['name'] ?? '';
        $description = $params['description'] ?? '';
        $logo = $params['logo'] ?? '';

        $slug = $this->generateSlug($name);

        return (new Store())
            ->setName($name)
            ->setSlug($slug)
            ->setDescription($description)
            ->setLogo($logo)
            ->setOwner($owner)
            ->setActive(true);
    }

    private function generateSlug(string $name): string
    {
        $slug = $this->sluggify($name);

        if ($slug && $this->storeRepository->findOneBySlug($slug)) {
            $slug .= '-' . uniqid();
        }
        return $slug;
    }

    private function sluggify(string $name): string
    {
        if (empty($name)) {
            return '';
        }
        // Convert to lowercase
        $slug = strtolower($name);
        // Remove special characters except alphanumeric and spaces
        $slug = preg_replace('/[^a-z0-9\s-]/', '', $slug);
        // Replace spaces with dashes
        $slug = preg_replace('/\s+/', '-', $slug);
        // Remove duplicate dashes
        $slug = preg_replace('/-+/', '-', $slug);
        // Trim dashes
        $slug = trim($slug, '-');

        return $slug;
    }
}
