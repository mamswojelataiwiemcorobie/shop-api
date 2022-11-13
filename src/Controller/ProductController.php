<?php

namespace App\Controller;

use App\DTO\CreateProductDto;
use App\Exception\ProductNotFoundException;
use App\Repository\ProductRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

#[Route(path: "/products", name: "products_")]
class ProductController extends AbstractController
{
    public function __construct(private ProductRepository $productRepository, private SerializerInterface $serializer, private ValidatorInterface $validator) {}

    #[Route(path: "", name: "all", methods: ["GET"])]
    function all(Request $request): Response
    {
        $page = $request->query->get('page') ?? 1;
        $data = $this->productRepository->findPaginated($page);
        return $this->json($data);
    }

    #[Route(path: "", name: "create", methods: ["POST"])]
    public function create(Request $request): Response
    {
        $data = $this->serializer->deserialize($request->getContent(), CreateProductDto::class, 'json');
        $errors = $this->validator->validate($data);
        if (count($errors) > 0) {
            throw new BadRequestHttpException((string) $errors);
        }
        $product = $this->productRepository->saveProduct($data->getTitle(), $data->getPrice(), $data->getCurrency());

        return $this->json([], Response::HTTP_CREATED, ["Location" => "/products/" . $product->getId()]);
    }

    #[Route(path:"/{id}", name:"update_product", methods:["PUT"])]
    public function update($id, Request $request): JsonResponse
    {
        $product = $this->productRepository->findOneBy(['id' => $id]);
        if (!$product) {
            throw new ProductNotFoundException($id);
        }
        $data = json_decode($request->getContent(), true);

        !empty($data['title']) ?: $product->setTitle($data['title']);
        !empty($data['price']) ?: $product->setPrice($data['price']);
        !empty($data['currency']) ?: $product->setCurrency($data['currency']);

        $this->productRepository->save($product, true);

        return new JsonResponse('Product updated', Response::HTTP_OK);
    }

    #[Route(path:"/{id}", name:"update_delete", methods:["DELETE"])]
    public function delete($id, Request $request): JsonResponse
    {
        $product = $this->productRepository->findOneBy(['id' => $id]);
        $this->productRepository->remove($product);
        return new JsonResponse('Product deleted', Response::HTTP_NO_CONTENT);
    }

}