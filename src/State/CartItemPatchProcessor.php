<?php

declare(strict_types = 1);

namespace App\State;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use App\ApiResource\CartItem\CartItemApi;
use App\Contracts\DateAndTimeInterface;
use App\Contracts\HttpResponseInterface;
use App\Contracts\InventoryServiceInterface;
use App\Entity\Cart;
use App\Entity\CartItem;
use App\Exception\CartItemNotFoundException;
use App\Exception\CartNotFoundException;
use App\Exception\InventoryException\InsufficientStockException;
use App\Exception\ProductChangeNotAllowedException;
use App\Repository\InventoryRepository;
use App\Service\Cart\CartItemService;
use DateTimeImmutable;
use Doctrine\ORM\EntityManagerInterface;

class CartItemPatchProcessor implements ProcessorInterface
{

    public function __construct (
        private readonly HttpResponseInterface $httpResponse,
        private readonly DtoToEntityStateProcessor $processor,

        private readonly EntityManagerInterface $entityManager,
        private readonly DateAndTimeInterface $dateAndTime,

        private readonly CartItemService $cartItemService,
        private readonly InventoryServiceInterface $inventoryService,
    )
    {
    }

    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = [])
    {
        assert($data instanceof CartItemApi);

        try {
            // check if the product is available in the inventory
            $this->inventoryService->checkInventory($data->product->id, $data->quantity);

            // get current date and time to set the updatedAt for both the cart and cartItem
            $currentDate = $this->getCurrentDateAndTime();

            // check if valid product is sent during Update ( we cannot change product during PATCH for /api/cart_items )
            $this->isValidProduct($context['previous_data'], $data);

            // Perform update of quantity
            $result = $this->processor->process($data, $operation, $uriVariables, $context);
            assert($result instanceof CartItemApi);

            /* Get the cart id and product id of the cart item we're trying to UPDATE using PATCH */
            $cartId    = $result->cart->id;
            $productId = $result->product->id;

            // get the cart and cart item
            $cart     = $this->getCart($cartId);
            $cartItem = $this->getCartItem($data->id);

            // set the updatedAt in both Cart and CartItem Table
            $cart->setUpdatedAt($currentDate);
            $cartItem->setUpdatedAt($currentDate);

            // set up the total price of specific cart item
            $total = $this->getTotalPrice($data->quantity, (string) $data->price_per_unit);
            $cartItem->setTotalPrice($total);

            // update in cart
            // to update in cart, sum up all the cart_item total_price associated with a cart
            $cartItemTotal = $this->cartItemService->calculateTotalPriceInCartItem(cartId: $cart->getId());
            $cart->setTotalPrice($cartItemTotal);

            // deduct the quantity from the database
            $this->inventoryService->deductQuantityFromInventory($productId, $data->quantity);

            // Persist the changes
            $this->entityManager->flush();

            // update actual total in response
            $data->totalPrice = $total;

            return $data;
        } catch (ProductChangeNotAllowedException) {
            return $this->httpResponse->invalidDataResponse(description: 'Product cannot be changed during PATCH request.');
        } catch (CartNotFoundException|CartItemNotFoundException $e) {
            return $this->httpResponse->notFoundException(description: $e->getMessage());
        } catch (InsufficientStockException) {
            return $this->httpResponse->invalidDataResponse(description: 'Not enough stock available.');
        }

    }

    public function isValidProduct($previousData, CartItemApi $data): void
    {
        // get the previous data
        assert($previousData instanceof CartItemApi);

        // get previous product id
        $previousProductId = $previousData->product->id;

        // get the new product id
        $newProductId = $data->product->id;

        if ($previousProductId !== $newProductId) {
            throw new ProductChangeNotAllowedException('Product cannot be replaced, please replace quantity only');
        }
    }

    private function getCart(int $cartId): Cart
    {
        $cart = $this->entityManager->find(Cart::class, $cartId);

        if (!$cart) {
            throw new CartNotFoundException("Cart with not found");
        }

        return $cart;
    }

    private function getCartItem(int $id): CartItem
    {

        $cartItem = $this->entityManager->find(CartItem::class, $id);

        if (!$cartItem) {
            throw new CartItemNotFoundException('Cart item not found');
        }

        return $cartItem;
    }

    private function getTotalPrice(int $quantity, string $pricePerUnit): string
    {
        return bcmul((string) $quantity, $pricePerUnit, 2);
    }

    private function getCurrentDateAndTime(): DateTimeImmutable
    {
        return new DateTimeImmutable('now', $this->dateAndTime->getTimeZone());
    }

}
