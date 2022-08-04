<?php
namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Response;
use Doctrine\Persistence\ManagerRegistry;
use App\Repository\OrderRepository;
use App\Entity\Order;
use App\Entity\Product;
use App\Entity\ProductOrder;

class OrderController extends AbstractController
{
     /**
     * Constructor
     *
     * @param OrderRepository $orderRepository
     */
    public function __construct(private ManagerRegistry $doctrine)
    {
    }

     /**
      * Create product
      *
      * @param Request $request
      * @return JsonResponse
      */
    #[Route('/order', methods: ['POST'], name: 'create_order')]
    public function createOrder(Request $request): JsonResponse {
        $userOrdersArray = $request->toArray();

        $order = new Order;

        $order->setCustomerName('Lorium');
        $order->setAddress('Epsume 35/2');
        $order->setCreatedAt(new \DateTime('now'));
        $order->setUpdatedAt(new \DateTime('now'));

        $this->doctrine->getRepository(Order::class)->add($order, true);

        foreach ($userOrdersArray as $userOrder) {
            $product = $this->doctrine->getRepository(Product::class)
                       ->find($userOrder['id']);

            $productOrder = new ProductOrder;

            $productOrder->setOrder($order);
            $productOrder->setProduct($product);
            $productOrder->setQuantity($userOrder['itemQty']);
            $productOrder->setCreatedAt(new \DateTime('now'));

            $existingQuantity    = $product->getQuantity();
            $afterDeductQuantity = ($existingQuantity - $userOrder['itemQty']);

            $product->setQuantity($afterDeductQuantity);

            $this->doctrine->getRepository(ProductOrder::class)
                 ->add($productOrder,true);
        }

        return new JsonResponse(
            [
                'status' => 1,
                'message' => 'Order created!'
            ],
            Response::HTTP_CREATED
        );
    }
}
