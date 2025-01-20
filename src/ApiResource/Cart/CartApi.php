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
use App\ApiResource\CartItem\CartItemApi;
use App\ApiResource\User\UserApi;
use App\Entity\Cart;
use App\State\EntityToDtoStateProvider;
use Symfony\Component\Validator\Constraints as Assert;

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
        new Post (
            security: 'is_granted("ADD", object)',
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

    /* cart status, */
    public ?string $status                  = null;

    // TODO:
    #[Assert\Regex(pattern: '/^\d{1,8}(\.\d{1,2})?$/')]
    public ?string $totalPrice              = null;

    /**
     * Another endpoint sets up the coupon code for Cart
     */
    #[Assert\Regex(
        pattern: '/^[a-zA-Z]+$/',
        message: 'The value must contain only letters.'
    )]
    public ?string $couponCode              = null;

    /**
     * @var CartItemApi[]
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