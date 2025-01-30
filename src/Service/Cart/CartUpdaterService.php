<?php

declare(strict_types = 1);

namespace App\Service\Cart;

use App\ApiResource\CartItem\CartItemApi;
use App\Contracts\DateAndTimeInterface;
use App\Contracts\InventoryServiceInterface;
use App\Contracts\PriceCalculatorServiceInterface;
use App\Entity\Cart;
use App\Entity\CartItem;
use App\Repository\CartItemRepository;
use App\Repository\CartRepository;
use App\Service\Cart\ErrorHandler\CartErrorHandler;
use App\Service\Cart\Validation\ProductValidator;
use App\State\DtoToEntityStateProcessor;
use Doctrine\ORM\EntityManagerInterface;
use Throwable;
use Symfony\Component\HttpFoundation\JsonResponse;

class CartUpdaterService
{

    public function __construct (
        private readonly EntityManagerInterface $entityManager,
        private readonly DateAndTimeInterface $dateAndTime,
        private readonly CartItemService $cartItemService,
        private readonly InventoryServiceInterface $inventoryService,
        private readonly PriceCalculatorServiceInterface $priceCalculator,
        private readonly ProductValidator $productValidator,
        private readonly CartRepository $cartRepository,
        private readonly CartItemRepository $cartItemRepository,
        private readonly CartErrorHandler $cartErrorHandler,
    )
    {
    }

    public function init(CartItemApi $data, DtoToEntityStateProcessor $processor, $context, $operation, $uriVariables): CartItemApi|JsonResponse
    {
        try {
            /* $cartItem is the data sent during the request */
            $this->inventoryService->checkInventory($data->product->id, $data->quantity);

            $this->productValidator->isValidProduct($context['previous_data'], $data);

            // update quantity
            $result = $processor->process($data, $operation, $uriVariables, $context);

            $this->updateCart($result, $data);

            return $data;
        } catch (Throwable $e) {
            return $this->cartErrorHandler->handleError($e);
        }
    }

    public function updateCart(CartItemApi $result, CartItemApi $data): void
    {

        // get the cart and cart item
        $cart     = $this->cartRepository->getCart($result->cart->id);
        $cartItem = $this->cartItemRepository->getCartItem($data->id);

        // set the updatedAt in both Cart and CartItem Table
        $this->setUpdatedAt($cart, $cartItem);

        // set up the total price of specific cartItem first so later we can set total for Cart\
        $total = $this->setTotalInCartItem($data, $cartItem);

        // update in cart
        // to update in cart, sum up all the cart_item total_price associated with a cart
        $this->setTotalsInCart($cart);

        // update actual total in response
        $data->totalPrice = $total;

        // Persist the changes
        $this->entityManager->flush();
    }

    private function setUpdatedAt(Cart $cart, CartItem $cartItem): void
    {
        $cart->setUpdatedAt($this->dateAndTime->getCurrentDateAndTime());
        $cartItem->setUpdatedAt($this->dateAndTime->getCurrentDateAndTime());
    }

    public function setTotalInCartItem(CartItemApi $data, CartItem $cartItem): string
    {
        $total = $this->priceCalculator->calculateTotalPrice($data->quantity, (string)$data->price_per_unit);

        $cartItem->setTotalPrice($total);

        return $total;
    }

    public function setTotalsInCart(Cart $cart): void
    {
        $cartItemTotal = $this->cartItemService->calculateTotalPriceInCartItem(cartId: $cart->getId());

        $cart->setTotalPrice($cartItemTotal);
    }

}