<?php

declare(strict_types = 1);

namespace App\Contracts\Cart;

use App\ApiResource\CartItem\CartItemApi;
use App\Entity\Cart;
use App\Entity\CartItem;
use App\Entity\User;

interface CartManagerInterface
{

    public function processCartOperation(User $user, CartItemApi $data): array;

    public function initializeCart(User $user, CartItemApi $data): array;

    public function createCart(User $user, CartItemApi $data): Cart;

    public function createCartItem(Cart $cart, CartItemApi $data): CartItem;

    public function updateExistingCart(Cart $cart, CartItemApi $data): CartItem;

    public function mapDataToCartItemApi(CartItemApi $data, Cart $cart): void;

    public function assignIdsToCartItemAndCart(CartItemApi $data, Cart $cart, CartItem $cartItem);

    public function initializeCartAndCartItem(User $user, CartItemApi $data): array;

}