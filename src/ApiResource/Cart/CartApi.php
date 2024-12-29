<?php

declare(strict_types = 1);

namespace App\ApiResource\Cart;

use ApiPlatform\Doctrine\Orm\State\Options;
use ApiPlatform\Metadata\ApiProperty;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Patch;
use ApiPlatform\Metadata\Post;
use App\ApiResource\User\UserApi;
use App\Entity\Cart;
use App\Entity\CartItem;
use App\State\EntityToDtoStateProvider;

#[ApiResource (
    shortName: 'Cart',
    description: 'Cart of a user',
    operations: [
        new GetCollection(
            security: 'is_granted("ROLE_ADMIN")'
        ),
        new Get (
            security: 'is_granted("VIEW", object)'
        ),
        new Patch (
            security: 'is_granted("EDIT", object)'
        ),
    ],
    provider: EntityToDtoStateProvider::class,
    stateOptions: new Options(entityClass: Cart::class)
)]
class CartApi
{

    #[ApiProperty(readable: true, writable: false, identifier: true)]
    public ?int $id                         = null;

    public ?UserApi $owner                  = null;

    public ?string $status                  = null;

    // TODO:
    public ?string $totalPrice              = null;

    /**
     * Another endpoint sets up the coupon code for Cart
     */
    public ?string $couponCode              = null;

    /**
     * @var CartItem[]
     */
    #[ApiProperty(readable: true, writable: false)]
    public ?array $cartItems                = null;

    public ?\DateTimeImmutable $createdAt   = null;

    public ?\DateTimeImmutable $updatedAt   = null;

    /**
     * Another endpoint sets up totalPriceAfterDiscount
     */
    public ?string $totalPriceAfterDiscount = null;

}