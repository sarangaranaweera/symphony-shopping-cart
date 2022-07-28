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
use App\Entity\OrderProduct;
use App\Repository\OrderProductRepository;
use App\Entity\Product;


class OrderController extends AbstractController
{
     /**
     * Constructor
     *
     * @param OrderRepository $orderRepository
     * @param OrderProductRepository $orderProductRepository
     */
    public function __construct(
        protected OrderRepository $orderRepository,
        protected OrderProductRepository $orderProductRepository,
    )
    {
    }

     /**
     * Create an order
     *
     * @param Request $request
     * @param ManagerRegistry $docrine
     * @return JsonResponse
     */
    #[Route('/order', methods: ['POST'], name: 'create_order')]
    public function createOrder(Request $request, 
                                ManagerRegistry $docrine,
                    ): JsonResponse
    {
        $data = $request->toArray();

        $order = new Order;
        $order->setCustomerName("Lorium");
        $order->setAddress("Epsume 35/2");
        $order->setCreatedAt(new \DateTime("now"));
        $order->setUpdatedAt(new \DateTime("now"));

        $this->orderRepository->add($order, true);

        foreach ($data as $userOrder) {
            $orderProduct = new OrderProduct;
            $orderProduct->setOrderId($order->getId());
            $orderProduct->setProductId($userOrder["id"]);
            $orderProduct->setQuantity($userOrder["itemQty"]);

            $product = $docrine->getRepository(Product::class)
                               ->find($userOrder["id"]);

            $existingQuantity    = $product->getQuantity();
            $afterDeductQuantity = ($existingQuantity - $userOrder["itemQty"]);

            $product->setQuantity($afterDeductQuantity);

            $this->orderProductRepository->add($orderProduct,true);
        }

        return new JsonResponse(
            ['message' => 'Order created!'],
            Response::HTTP_CREATED
        );
    }
}
