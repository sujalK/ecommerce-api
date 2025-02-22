<?php

declare(strict_types = 1);

namespace App\ApiResource\Wishlist;

use ApiPlatform\Doctrine\Orm\State\Options;
use ApiPlatform\Metadata\ApiProperty;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Delete;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Post;
use App\ApiResource\Product\ProductApi;
use App\ApiResource\User\UserApi;
use App\Entity\Wishlist;
use App\State\CreateWishlistStateProcessor;
use App\State\DtoToEntityStateProcessor;
use App\State\EntityToDtoStateProvider;
use App\Validator\IsUniqueProduct;
use App\Validator\IsValidOwner;
use App\Validator\IsValidProduct;
use Symfony\Component\Validator\Constraints as Assert;

#[ApiResource(
    shortName: 'Wishlist',
    description: 'Wishlist of a User',
    operations: [
        new Get(),
        new GetCollection(),
        new Post(
            validationContext: ['groups' => ['Default', 'postValidation']],
            processor: CreateWishlistStateProcessor::class,
        ),
        new Delete(
            security: 'is_granted("DELETE", object)',
        ),
    ],
    paginationItemsPerPage: 10,
    security: 'is_granted("ROLE_USER")',
    provider: EntityToDtoStateProvider::class,
    processor: DtoToEntityStateProcessor::class,
    stateOptions: new Options(entityClass: Wishlist::class)
)]
class WishlistApi
{

    #[ApiProperty(readable: true, writable: false, identifier: true)]
    public ?int $id                       = null;

    #[ApiProperty(readable: false, writable: false)]
    #[IsValidOwner]
    public ?UserApi $ownedBy              = null;

    #[Assert\NotBlank(groups: ['postValidation'])]
    #[IsValidProduct]
    #[IsUniqueProduct]
    public ?ProductApi $product           = null;

    #[ApiProperty(readable: false, writable: false)]
    public ?\DateTimeImmutable $createdAt = null;

}