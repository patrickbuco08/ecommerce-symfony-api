<?php

namespace Bocum\Controller;

use Bocum\Repository\StoreRepository;
use Bocum\Transformer\StoreTransformer;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class StoreController extends AbstractController
{
    public function __construct(
        private StoreRepository $storeRepository,
        private StoreTransformer $storeTransformer,
        private EntityManagerInterface $entityManager,
        private \Bocum\Service\StoreService $storeService,
        private \Bocum\Factory\StoreFactory $storeFactory,
        private ValidatorInterface $validator,
    ) {}

    #[Route('/api/stores', name: 'list_stores', methods: ['GET'])]
    public function listStores(Request $request): JsonResponse
    {
        $stores = $this->storeService->getPaginatedActiveStores($request);

        return new JsonResponse([
            'page' => $stores->page,
            'total_pages' => $stores->totalPages,
            'total_results' => $stores->totalResults,
            'stores' => $this->storeTransformer->transformCollection($stores->results)
        ], JsonResponse::HTTP_OK);
    }

    #[Route('/api/stores/{slug}', name: 'get_store', methods: ['GET'])]
    public function getStore(string $slug): JsonResponse
    {
        $store = $this->storeRepository->findOneBySlug($slug);

        if (!$store) {
            return new JsonResponse(['error' => 'Store not found'], JsonResponse::HTTP_NOT_FOUND);
        }
        return new JsonResponse($this->storeTransformer->transform($store));
    }

    #[Route('/api/stores', name: 'create_store', methods: ['POST'])]
    #[IsGranted('ROLE_USER')]
    public function createStore(Request $request, UserInterface $user): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        $store = $this->storeFactory->create($data, $user);
        $errors = $this->validator->validate($store);

        if (count($errors) > 0) {
            $errorMessages = [];
            foreach ($errors as $error) {
                $errorMessages[$error->getPropertyPath()] = $error->getMessage();
            }
            return new JsonResponse(['errors' => $errorMessages], JsonResponse::HTTP_UNPROCESSABLE_ENTITY);
        }

        $this->entityManager->persist($store);
        $this->entityManager->flush();

        return new JsonResponse($this->storeTransformer->transform($store), JsonResponse::HTTP_CREATED);
    }
}
