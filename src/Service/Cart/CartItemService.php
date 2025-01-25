<?php

declare(strict_types = 1);

namespace App\Service\Cart;

use App\Repository\CartItemRepository;

class CartItemService
{

    public function __construct (
        private readonly CartItemRepository $cartItemRepository,
    ) {
    }

    public function calculateTotalPriceInCartItem(int $cartId): string
    {
        // total Price
        $totalPrice = '0';
        $cartItems  = $this->cartItemRepository->findBy(['cart' => $cartId]);

        // Loop over each cart item to calculate sum
        foreach($cartItems as $cartItem) {
            $totalPrice = bcadd($totalPrice, $cartItem->getTotalPrice(), 2);
        }

        // return the total sum
        return $totalPrice;
    }

}