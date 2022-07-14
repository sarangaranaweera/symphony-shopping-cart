<?php
namespace App\Controller;


use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Response;
use App\Entity\Product;
use App\Repository\ProductRepository;
use Symfony\Component\HttpFoundation\Request;

class ProductController extends AbstractController
{

    public function __construct(protected ProductRepository $productRepository)
    {
    }

    #[Route('/product', methods: ['POST'], name: 'create_product')]
    public function createProduct(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        $name = $data["name"];
        $image = $data["image"];
        $price = $data["price"];

        //validate data and throw exception
        if (empty($name) || empty($image) || empty($price)) {
            throw $this->createNotFoundException(
                'Expecting required parameters!'
            );
        }

        $product = new Product;
        $product->setName($name);
        $product->setImage($image);
        $product->setPrice($price);

        $this->productRepository->add($product, true);

        return new JsonResponse(['msg' => 'Product created!'], Response::HTTP_CREATED);
    }

    #[Route('/products/{id}', methods: ['GET'], name: 'find_product')]
    public function findProduct($id): JsonResponse
    {
        $product = $this->productRepository->findOneBy(['id' => $id]);

        if (!$product) {
            return throw $this->createNotFoundException(
                'No product found for id ' . $id
            );
        }

        $data = [
            "id" => $product->getId(),
            "name" => $product->getName(),
            "image" => $product->getImage(),
            "price" => $product->getPrice(),
        ];

        return new JsonResponse($data, Response::HTTP_OK);
    }

    #[Route('/products', methods: ['GET'], name: 'find_all_products')]
    public function findAllProduct(): JsonResponse
    {
        $products = $this->productRepository->findAll();

        $data = [];

        foreach ($products as $product) {
            $data[] = [
                "id" => $product->getId(),
                "name" => $product->getName(),
                "image" => $product->getImage(),
                "price" => $product->getPrice(),
            ];
        }

        return new JsonResponse($data, Response::HTTP_OK);
    }

    #[Route('/products/{id}', methods: ['PUT'], name: 'update_products')]
    public function updateProduct($id, Request $request): JsonResponse
    {
        $product = $this->productRepository->findOneBy(['id' => $id]);
        if (!$product) {
            return throw $this->createNotFoundException(
                'No product found to delete for ' . $id
            );
        }
        $data = json_decode($request->getContent(), true);

        empty($data['name']) ? true : $product->setName($data['name']);
        empty($data['image']) ? true : $product->setImage($data['image']);
        empty($data['price']) ? true : $product->setImage($data['price']);

        $updatedProduct = $this->productRepository->updateProduct($product);

        $data = [
            "name" => $updatedProduct->getName(),
            "image" => $updatedProduct->getImage(),
            "price" => $updatedProduct->getPrice(),
        ];

        return new JsonResponse($data, Response::HTTP_OK);
    }

    #[Route('/products/{id}', methods: ['DELETE'], name: 'delete_product')]
    public function deleteProduct($id): JsonResponse
    {
        $product = $this->productRepository->findOneBy(['id' => $id]);

        if (!$product) {
            return throw $this->createNotFoundException(
                'No product found to delete for ' . $id
            );
        }

        $this->productRepository->remove($product, true);

        return new JsonResponse(['msg' => 'Product deleted!'], Response::HTTP_NO_CONTENT);
    }
}
