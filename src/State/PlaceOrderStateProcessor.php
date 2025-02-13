<?php

declare(strict_types = 1);

namespace App\State;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use App\ApiResource\Order\OrderApi;
use App\ApiResource\User\UserApi;
use App\Contracts\HttpResponseInterface;
use App\Entity\Cart;
use App\Entity\Order;
use App\Entity\OrderItem;
use App\Exception\CartNotFoundException;
use App\Repository\CartItemRepository;
use App\Repository\CartRepository;
use App\Repository\OrderRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfonycasts\MicroMapper\MicroMapperInterface;

class PlaceOrderStateProcessor implements ProcessorInterface
{

    public function __construct (
        private readonly Security $security,
        private readonly CartRepository $cartRepository,
        private readonly CartItemRepository $cartItemRepository,
        private readonly OrderRepository $orderRepository,
        private readonly EntityManagerInterface $entityManager,
        private readonly MicroMapperInterface $microMapper,
        private readonly HttpResponseInterface $httpResponse,
        private readonly DtoToEntityStateProcessor $innerProcessor,
    )
    {
    }

    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = [])
    {
        assert($data instanceof OrderApi);

        $user = $this->security->getUser();

        try {
            $cart = $this->getCartOfLoggedInUser($user);
        } catch (CartNotFoundException) {
            return $this->httpResponse->notFoundException();
        }

        // set the owner
        $data->ownedBy = $this->microMapper->map($user, UserApi::class, [
            MicroMapperInterface::MAX_DEPTH => 1,
        ]);
        $data->totalPrice = $cart->getTotalPrice();
        $data->couponCode = $cart->getCouponCode();
        $data->currency   = $cart->getCurrency();

        // transfer "cart_item" to "order_item"

        /* Create Order and return the result */
        $result = $this->innerProcessor->process($data, $operation, $uriVariables, $context);

        // dd($result);
        assert($result instanceof OrderApi);

        // After Order is created, create "OrderItem" instance for each cart item,
        $cartItems = $this->cartItemRepository->findBy(['cart' => $cart]);

        // dd($result);
        if ( ! empty($cartItems) ) {
            $order = $this->orderRepository->findOneBy(['id' => $result->id]);
            // dd($order);

            foreach($cartItems as $cartItem) {
                $orderItem = new OrderItem();
                $orderItem->setUnitPriceAfterDiscount($cartItem->getDiscountAmount());
                $orderItem->setTotalPrice($cartItem->getTotalPrice());
                $orderItem->setQuantity($cartItem->getQuantity());
                $orderItem->setProduct($cartItem->getProduct());
                $orderItem->setOrder (
                    $this->microMapper->map($result, Order::class, [
                        MicroMapperInterface::MAX_DEPTH => 0,
                    ])
                );
                $orderItem->setUnitPrice($cartItem->getPricePerUnit());

                $this->entityManager->persist($orderItem);
            }
        }

        // delete all associated cart item
        $this->cartItemRepository->deleteByCart($cart);

        // remove the cart from the database
        $this->entityManager->remove($cart);
        $this->entityManager->flush();

        return [
            'status' => 'Order Placed successful, please make payment to confirm your order.',
        ];

    }

    public function getCartOfLoggedInUser(?UserInterface $user): Cart
    {
        // get the current active cart of a user
        $cart = $this->cartRepository->findOneBy(criteria: ['owner' => $user, 'status' => 'active'], orderBy: ['createdAt' => 'DESC']);

        if (!$cart) {
            throw new CartNotFoundException();
        }

        return $cart;
    }
}
