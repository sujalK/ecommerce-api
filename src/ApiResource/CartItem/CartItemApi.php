<?php

declare(strict_types = 1);

namespace App\ApiResource\CartItem;

use ApiPlatform\Doctrine\Orm\State\Options;
use ApiPlatform\Metadata\ApiProperty;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Delete;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Patch;
use ApiPlatform\Metadata\Post;
use App\ApiResource\Cart\CartApi;
use App\ApiResource\Product\ProductApi;
use App\Entity\CartItem;
use App\State\CartItemPatchProcessor;
use App\State\CartItemStateProcessor;
use App\State\DeleteCartItemProcessor;
use App\State\EntityToDtoStateProvider;
use Symfony\Component\Validator\Constraints as Assert;

#[ApiResource (
    shortName: 'CartItem',
    description: 'Cart items associated with cart',
    operations: [
        new Get(),
        new GetCollection(),
        new Patch (
            security: 'is_granted("PATCH", object)',
            validationContext: ['groups' => ['Default', 'patchValidation']],
            processor: CartItemPatchProcessor::class
        ),
        new Post (
            uriTemplate: '/carts',
            validationContext: ['groups' => ['Default', 'postValidation']],
            processor: CartItemStateProcessor::class,
        ),
        new Delete (
            security: 'is_granted("DELETE", object)',
            processor: DeleteCartItemProcessor::class,
        ),
    ],
    formats: [
        'json' => 'application/json',
    ],
    security: 'is_granted("ROLE_USER")',
    provider: EntityToDtoStateProvider::class,
    stateOptions: new Options(entityClass: CartItem::class)
)]
class CartItemApi
{

    #[ApiProperty(readable: false, writable: false, identifier: true)]
    public ?int $id                         = null;

    #[ApiProperty(readable: true, writable: false)]
    public ?CartApi $cart                   = null;

    /**
     * only product, and quantity is required when we add item to the cart
     */
    #[Assert\NotBlank(groups: ['postValidation'])]
    public ?ProductApi $product             = null;

    #[Assert\NotBlank(groups: ['postValidation', 'patchValidation'])]
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

    #[ApiProperty(writable: false, security: 'is_granted("ROLE_ADMIN")')]
    public bool $isAdmin = true;

}