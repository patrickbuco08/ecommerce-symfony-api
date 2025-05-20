<?php

namespace Bocum\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\CurrentUser;
use Bocum\Entity\User;
use Bocum\Service\UserService;
use Bocum\Transformer\UserTransformer;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\HttpFoundation\Request;


#[Route('/api/profile')]
class ProfileController extends AbstractController
{
    public function __construct(private UserService $userService, private UserTransformer $userTransformer) {}

    #[Route('', name: 'api_profile', methods: ['GET'])]
    public function profile(#[CurrentUser] ?User $user): JsonResponse
    {
        if (!$user) {
            return new JsonResponse(['error' => 'Unauthorized'], JsonResponse::HTTP_UNAUTHORIZED);
        }

        return new JsonResponse($this->userTransformer->transform($user));
    }

    #[Route('', name: 'update_profile', methods: ['PUT'])]
    #[IsGranted('ROLE_USER')]
    public function updateProfile(Request $request, #[CurrentUser] User $user): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        $result = $this->userService->update($user, $data);

        return new JsonResponse($result);
    }
}
