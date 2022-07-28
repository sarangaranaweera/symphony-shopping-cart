<?php
namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Exception;

use App\Entity\Product;
use App\Repository\ProductRepository;

/**
 * CRUD oparations for product
 * 
 * @author Name <saranga@360-css.com>
 */
class ProductController extends AbstractController
{
    /**
     * Constructor
     *
     * @param ProductRepository $productRepository
     */
    public function __construct(protected ProductRepository $productRepository)
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
        $data = $request->toArray();

        $this->dataValidation($data);

        $name     = $data["name"];
        $image    = $data["image"];
        $price    = $data["price"];
        $quantity = $data["quantity"];

        $product = new Product;
        $product->setName($name);
        // $product->setImage($image);
        $product->setImage('https://via.placeholder.com/50x50');
        $product->setPrice($price);
        $product->setQuantity($quantity);

        $this->productRepository->add($product, true);

        return new JsonResponse(
            ['message' => 'Product created!'],
            Response::HTTP_CREATED
        );
    }

    /**
     * Find a product from id
     * 
     * @param integer $id
     * @return JsonResponse
     */
    #[Route('/products/{id}', methods: ['GET'], name: 'find_product')]
    public function findAProduct($id): JsonResponse
    {
        if (empty($id) || is_null($id) || !is_numeric($id)) {
            throw new Exception("Product id not found", 1);
        }

        $product = $this->productRepository->findOneBy(['id' => $id]);

        if (!$product) {
            throw new Exception('No product found for id ' . $id, 0);
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
        $products = $this->productRepository->findAll();
        $data     = [];

        foreach ($products as $product) {
            $data[] = [
                'id'       => $product->getId(),
                'name'     => $product->getName(),
                'image'    => $product->getImage(),
                'price'    => $product->getPrice(),
                'quantity' => $product->getQuantity(),
                'selected' => false
            ];
        }

        return new JsonResponse($data, Response::HTTP_OK);
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
        //Data validation part
        $product = $this->productRepository->findOneBy(['id' => $id]);

        if (!$product) {
            return throw $this->createNotFoundException(
                "No product found to delete for " . $id
            );
        }

        $data = json_decode($request->getContent(), true);

        empty($data["name"])     ? true : $product->setName($data["name"]);
        empty($data["image"])    ? true : $product->setImage($data["image"]);
        empty($data["price"])    ? true : $product->setPrice($data["price"]);
        empty($data["quantity"]) ? true : $product->setQuantity(
                                                        $data["quantity"]
                                                    );

        $updatedProduct = $this->productRepository->updateProduct($product);

        $data = [
            'name'     => $updatedProduct->getName(),
            'image'    => $updatedProduct->getImage(),
            'price'    => $updatedProduct->getPrice(),
            'quantity' => $updatedProduct->getQuantity(),
        ];

        return new JsonResponse($data, Response::HTTP_OK);
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
        $product = $this->productRepository->findOneBy(['id' => $id]);

        if (!$product) {
            return throw $this->createNotFoundException(
                "No product found to delete for " . $id
            );
        }

        $this->productRepository->remove($product, true);

        return new JsonResponse(
            ['message' => "Product deleted!"], Response::HTTP_NO_CONTENT
        );
    }

    private function dataValidation(array $data): void
    {
        if(isset($data['name'],
                 $data['image'],
                 $data['price'],
                 $data['quantity'])){
            if(empty($data['name']) ||
               is_null($data['name']) ||
               ctype_space($data['name'])){
                throw new Exception("Name field can not be null or empty", 1);
            }elseif(empty($data['image']) ||
                    is_null($data['image']) ||
                    ctype_space($data['image'])){
                throw new Exception("Image field can not be null or empty", 1);
            }elseif(!is_numeric($data['price']) ||
                    is_null($data['price']) ||
                    ctype_space($data['price'])){
                throw new Exception("Price field should be numeric value", 1);
            }elseif(!is_numeric($data['quantity']) ||
                    is_null($data['quantity']) ||
                    ctype_space($data['quantity'])){
                throw new Exception(
                    "Quantity field should be numeric value", 1);
            }
        }else{
            throw new Exception("All field are required", 1);
        }
    }
}
