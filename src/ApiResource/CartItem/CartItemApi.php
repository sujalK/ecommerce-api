<?php

declare(strict_types = 1);

namespace App\ApiResource\CartItem;

use ApiPlatform\Metadata\ApiProperty;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Delete;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Patch;
use ApiPlatform\Metadata\Post;
use App\ApiResource\Cart\CartApi;
use App\ApiResource\Product\ProductApi;
use App\State\CartItemStateProcessor;
use App\State\EntityToDtoStateProvider;
use Symfony\Component\Validator\Constraints as Assert;

#[ApiResource (
    shortName: 'CartItem',
    description: 'Cart items associated with cart',
    operations: [
        new Get(),
        new GetCollection(),
        new Patch (
            denormalizationContext: [ 'groups' => ['cart_item:patch'] ],
            validationContext:      [ 'groups' => ['cart_item:patch'] ]
        ),
        new Post (
            uriTemplate: '/carts',
            processor: CartItemStateProcessor::class
        ),
        new Delete(),
    ],
    formats: [
        'json' => 'application/json',
    ],
    security: 'is_granted("ROLE_USER")',
    provider: EntityToDtoStateProvider::class,
)]
class CartItemApi
{

    #[ApiProperty(readable: false, writable: false, identifier: true)]
    public ?int $id                         = null;

    #[ApiProperty(readable: true, writable: false)]
    public ?CartApi $cart                   = null;

    /**
     * only product is required when we add item to the cart
     */
    #[Assert\NotBlank]
    public ?ProductApi $product             = null;

    #[Assert\NotBlank]
    public ?int $quantity                   = null;

    #[ApiProperty(readable: true, writable: false)]
    public ?string $price_per_unit          = null;

    #[ApiProperty(readable: true, writable: false)]
    public ?string $totalPrice              = null;

    /**
     * Both the discountAmount, and totalPriceAfterDiscount is to be handled by different endpoint
     */
    public ?string $discountAmount          = null;

    public ?string $totalPriceAfterDiscount = null;

}