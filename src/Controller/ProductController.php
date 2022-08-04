<?php
namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Exception;
use App\Entity\Product;
use Doctrine\Persistence\ManagerRegistry;

/**
 * CRUD oparations for product
 * 
 * @author Name <saranga@360-css.com>
 */
class ProductController extends AbstractController
{
    /**
     * Undocumented function
     *
     * @param ManagerRegistry $doctrine
     */
    public function __construct(private ManagerRegistry $doctrine)
    {
    }

    /**
     * Create a product
     *
     * @param Request $request
     * @return JsonResponse
     */
    #[Route('/product', methods: ['POST'], name: 'create_product')]
    public function createProduct(Request $request): JsonResponse
    {
        $productDetails = $request->toArray();

        $this->dataValidation($productDetails);

        $name     = $productDetails['name'];
        $image    = $productDetails['image'];
        $price    = $productDetails['price'];
        $quantity = $productDetails['quantity'];

        $product = new Product;

        $product->setName($name);
        $product->setImage('https://via.placeholder.com/50x50');
        $product->setPrice($price);
        $product->setQuantity($quantity);

        $this->doctrine->getRepository(Product::class)->add($product, true);

        return new JsonResponse(
            ['message' => 'Product created!'],
            Response::HTTP_CREATED
        );
    }

    /**
     * Find product from id
     * 
     * @param integer $id
     * @return JsonResponse
     */
    #[Route('/products/{id}', methods: ['GET'], name: 'find_product')]
    public function findProduct($id): JsonResponse
    {
        if (empty($id) || is_null($id) || !is_numeric($id)) {
            throw new Exception('Product id not found', 400);
        }

        // $product = $this->productRepository->find($id);
        $product = $this->doctrine->getRepository(Product::class)->find($id);

        if (!$product) {
            throw new Exception("No product found for id $id", 400);
        }

        $data = [
            'id'       => $product->getId(),
            'name'     => $product->getName(),
            'image'    => $product->getImage(),
            'price'    => $product->getPrice(),
            'quantity' => $product->getQuantity(),
            'selected' => false,
        ];

        return new JsonResponse($data, Response::HTTP_OK);
    }

    /**
     * Get all products
     *
     * @return JsonResponse
     */
    #[Route('/products', methods: ['GET'], name: 'find_all_products')]
    public function findAllProducts(): JsonResponse
    {
        // $products = $this->productRepository->findAll();
        $products = $this->doctrine->getRepository(Product::class)->findAll();
        $organizedProduct = [];

        foreach ($products as $product) {
            $organizedProduct[] = [
                'id'       => $product->getId(),
                'name'     => $product->getName(),
                'image'    => $product->getImage(),
                'price'    => $product->getPrice(),
                'quantity' => $product->getQuantity(),
                'selected' => false
            ];
        }

        return new JsonResponse($organizedProduct, Response::HTTP_OK);
    }

    /**
     * Update product
     * 
     * @param Request $request
     * @param integer $id
     * @return JsonResponse
     */
    #[Route('/products/{id}', methods: ['PUT'], name: 'update_products')]
    public function updateProduct($id, Request $request): JsonResponse
    {
        $productDetails = $request->toArray();

        $this->dataValidation($productDetails);

        // $product = $this->productRepository->find($id);
        $product = $this->doctrine->getRepository(Product::class)->find($id);

        if (!$product) {
            throw new Exception("No product found to delete for $id", 400);
        }

        $product->setName($productDetails["name"]);
        $product->setImage($productDetails["image"]);
        $product->setPrice($productDetails["price"]);
        $product->setQuantity($productDetails["quantity"]);

        // $updatedProduct = $this->productRepository->updateProduct($product);
        $updatedProduct = $this->doctrine->getRepository(
            Product::class
        )->updateProduct($product);

        $organizedUpdatedProduct = [
            'name'     => $updatedProduct->getName(),
            'image'    => $updatedProduct->getImage(),
            'price'    => $updatedProduct->getPrice(),
            'quantity' => $updatedProduct->getQuantity(),
        ];

        return new JsonResponse($organizedUpdatedProduct, Response::HTTP_OK);
    }

    /**
     * Delete product
     * 
     * @param integer $id
     * @return JosnResponse
     */
    #[Route('/products/{id}', methods: ['DELETE'], name: 'delete_product')]
    public function deleteProduct($id): JsonResponse
    {
        if (!is_numeric($id) || (int) $id < 0) {
            throw new Exception('Product is should be an integer value');
        }

        $product = $this->doctrine->getRepository(Product::class)->find($id);

        if (!$product) {
            throw new Exception("No product found to delete for $id", 400);
        }

        $this->doctrine->getRepository(Product::class)->remove($product, true);

        return new JsonResponse(
            ['message' => "Product deleted!"],
            Response::HTTP_NO_CONTENT
        );
    }

    /**
     * product inputs validation
     *
     * @param array $data
     * @return void
     */
    private function dataValidation(array $data): void
    {
        if (
            isset($data['name'],
            $data['image'],
            $data['price'],
            $data['quantity'])
        ) {
            if (
                empty($data['name']) ||
                is_null($data['name']) ||
                ctype_space($data['name'])
            ) {
                throw new Exception("Name field can not be null or empty", 400);

            } elseif (
                    empty($data['image']) ||
                    is_null($data['image']) ||
                    ctype_space($data['image'])
              ) {
                throw new Exception(
                    "Image field can not be null or empty", 400
                );

            } elseif (
                    !is_numeric($data['price']) ||
                    is_null($data['price'])
              ) {
                throw new Exception("Price field should be numeric value", 400);

            } elseif (
                    !is_numeric($data['quantity']) ||
                    is_null($data['quantity']) ||
                    ctype_space($data['quantity'])
              ) {
                throw new Exception(
                    "Quantity field should be numeric value", 400
                );
            }
        } else {
            throw new Exception('All field are required', 400);
        }
    }
}
