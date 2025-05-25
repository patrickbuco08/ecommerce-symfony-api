<?php

namespace Bocum\Controller\Admin;

use Bocum\Entity\Product;
use Bocum\Form\ProductType;
use Bocum\Service\ProductService;
use Bocum\Transformer\ProductTransformer;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Validator\Exception\ValidatorException;

#[Route('/api/admin/products')]
#[IsGranted('ROLE_ADMIN')]
class ProductController extends AbstractController
{
    public function __construct(
        private ProductService $productService,
        private FormFactoryInterface $formFactory,
        private ProductTransformer $productTransformer,
    ) {}

    #[Route('', name: 'create_product', methods: ['POST'])]
    public function createProduct(
        Request $request,
    ): JsonResponse {
        $form = $this->formFactory->create(ProductType::class);
        $form->submit(json_decode($request->getContent(), true));

        if (!$form->isValid()) {
            $errors = [];
            foreach ($form->getErrors(true) as $error) {
                $errors[] = $error->getMessage();
            }

            return new JsonResponse(['errors' => $errors], 400);
        }

        try {
            $product = $this->productService->create($form->getData());
            $data = $this->productTransformer->transform($product);

            return new JsonResponse($data, JsonResponse::HTTP_CREATED);
        } catch (NotFoundHttpException $e) {
            return new JsonResponse(['error' => $e->getMessage()], JsonResponse::HTTP_BAD_REQUEST);
        } catch (ValidatorException $e) {
            return new JsonResponse(['error' => $e->getMessage()], JsonResponse::HTTP_BAD_REQUEST);
        }
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
