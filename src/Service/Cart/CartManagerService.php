<?php

declare(strict_types = 1);

namespace App\Service\Cart;

use App\ApiResource\Cart\CartApi;
use App\ApiResource\CartItem\CartItemApi;
use App\Contracts\Cart\CartManagerInterface;
use App\Contracts\InventoryServiceInterface;
use App\Contracts\PersistenceServiceInterface;
use App\Contracts\DateAndTimeInterface;
use App\Contracts\PriceCalculatorServiceInterface;
use App\Entity\Cart;
use App\Entity\CartItem;
use App\Entity\Product;
use App\Entity\User;
use App\Enum\CartStatus;
use App\Exception\InvalidQuantityException;
use App\Exception\InventoryException\InsufficientStockException;
use App\Exception\InventoryException\ProductNotFoundException;
use App\Repository\CartRepository;
use Symfonycasts\MicroMapper\MicroMapperInterface;

class CartManagerService implements CartManagerInterface
{

    public function __construct (
        private readonly MicroMapperInterface            $microMapper,
        private readonly DateAndTimeInterface            $dateAndTime,
        private readonly CartRepository                  $cartRepository,
        private readonly PersistenceServiceInterface     $persistenceService,
        private readonly PriceCalculatorServiceInterface $priceCalculatorService,
        private readonly InventoryServiceInterface       $inventoryService,
    )
    {
    }

    /**
     * @throws InsufficientStockException
     * @throws ProductNotFoundException
     */
    public function processCartOperation(User $user, CartItemApi $data): array
    {
        if ($data->quantity <= 0) {
            throw new InvalidQuantityException();
        }

        [$cart, $cartItem] = $this->initializeCartAndCartItem($user, $data);

        $this->mapDataToCartItemApi($data, $cart);

        $this->assignIdsToCartItemAndCart($data, $cart, $cartItem);

        return [$cart, $cartItem];
    }

    public function initializeCart(User $user, CartItemApi $data): array
    {
        $cart     = $this->createCart(user: $user, data: $data);
        $cartItem = $this->createCartItem(cart: $cart, data: $data);

        // Added part
        $this->syncWithDB($cart);
        $this->syncWithDB($cartItem);

        return [$cart, $cartItem];
    }

    public function createCart(User $user, CartItemApi $data): Cart
    {
        $cart = new Cart();

        $cart->setOwner($user);
        $cart->setStatus(CartStatus::ACTIVE->value);
        $cart->setCreatedAt(new \DateTimeImmutable('now', $this->dateAndTime->getTimeZone()));
        // $cart->setTotalPrice($this->priceCalculatorService->calculateTotalPrice($data->quantity, $data->product->price));

        return $cart;
    }

    public function createCartItem(Cart $cart, CartItemApi $data): CartItem
    {
        // Check stock availability before creating the cart item
        $this->inventoryService->checkInventory($data->product->id, $data->quantity);

        $cartItem = new CartItem();
        $cartItem->setCart($cart);
        $cartItem->setProduct(
            // transforming the ProductApi (object that's mapped from the user sent data to ProductApi using Provider) -> Product Entity
            $this->microMapper->map($data->product, Product::class, [
                MicroMapperInterface::MAX_DEPTH => 0,
            ])
        );
        $cartItem->setQuantity($data->quantity);
        $cartItem->setPricePerUnit($data->product->price);
        $cartItem->setTotalPrice($this->priceCalculatorService->calculateTotalPrice($data->quantity, $data->product->price));

        return $cartItem;
    }

    public function updateExistingCart(Cart $cart, CartItemApi $data): CartItem
    {
        $cart->setUpdatedAt(new \DateTimeImmutable('now', $this->dateAndTime->getTimeZone()));

        $productId = $data->product->id;
        $cartItem = \array_find($cart->getCartItems()->getValues(), static fn(CartItem $cartItem) => $cartItem->getProduct()->getId() === $productId);

        if ( $cartItem ) {
            assert($cartItem instanceof CartItem);
            $newQuantity = $cartItem->getQuantity() + $data->quantity;
            $newPrice    = $newQuantity * $cartItem->getPricePerUnit();

            $this->inventoryService->checkInventory($data->product->id, $data->quantity);

            // update cart Item
            $cartItem->setQuantity($newQuantity);
            $cartItem->setTotalPrice((string) ($newPrice));
            $cartItem->setUpdatedAt(new \DateTimeImmutable(datetime: 'now', timezone: $this->dateAndTime->getTimeZone()));

        } else {
            $this->inventoryService->checkInventory($data->product->id, $data->quantity);

            // create non-existing cartItem and map to $cart (existing cart)
            $cartItem = $this->createCartItem($cart, $data);

        }

        $this->syncWithDB($cartItem);
        $this->syncWithDB($cart);

        return $cartItem;
    }

    public function mapDataToCartItemApi(CartItemApi $data, Cart $cart): void
    {
        $data->price_per_unit = $data->product->price;
        $data->totalPrice = $this->priceCalculatorService->calculateTotalPrice($data->quantity, $data->product->price);
        $data->cart           = $this->microMapper->map($cart, CartApi::class, [
            MicroMapperInterface::MAX_DEPTH => 0,
        ]);
    }

    public function assignIdsToCartItemAndCart(CartItemApi $data, Cart $cart, CartItem $cartItem)
    {
        $data->id = $cartItem->getId();

        if ( ! isset($data->cart?->id) )
            $data->cart->id = $cart->getId();
    }

    public function initializeCartAndCartItem(User $user, CartItemApi $data): array
    {
        // Check if cart exists for the logged-in user
        $cart = $this->cartRepository->findOneBy(['owner' => $user, 'status' => CartStatus::ACTIVE->value]);

        if (!$cart) {
            /** Initially, if there is no cart */
            [$cart, $cartItem] = $this->initializeCart($user, $data);
        } else {
            /** If there is already an existing cart */
            $cartItem = $this->updateExistingCart($cart, $data);
        }

        // update cart table
        $cart->setTotalPrice($this->getTotalPrice($data, $cartItem, $cart));

        // Persist the changes
        $this->syncWithDB($cart);

        $this->inventoryService->deductQuantityFromInventory($data->product->id, $data->quantity);

        return [$cart, $cartItem];
    }

    public function getTotalPrice(CartItemApi $data, mixed $cartItem, mixed $cart): string
    {
        $quantity     = $data->quantity; // quantity that is being added to the cart
        $pricePerUnit = (int) $cartItem->getPricePerUnit(); // Get unit price for specific cart item
        $totalPrice   = $cart->getTotalPrice(); // Get previous cart total

        $totalPriceForProduct = $this->calculateTotalsForProduct($quantity, $pricePerUnit);

        // Calculate total price with precision
        return bcadd((string)$totalPrice, $totalPriceForProduct, 2);
    }

    private function syncWithDB(object $object): void
    {
        $this->persistenceService->sync($object);
    }

    private function calculateTotalsForProduct(int $quantity, int $pricePerUnit): string
    {
        return bcmul((string)$quantity, (string) $pricePerUnit, 2);
    }

}