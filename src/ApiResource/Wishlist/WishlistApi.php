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
use App\State\DtoToEntityStateProcessor;
use App\State\EntityToDtoStateProvider;
use Symfony\Component\Validator\Constraints as Assert;

#[ApiResource(
    shortName: 'Wishlist',
    description: 'Wishlist of a User',
    operations: [
        new Get(),
        new GetCollection(),
        new Post(),
        new Delete(),
    ],
    paginationItemsPerPage: 10,
    security: 'is_granted("WISHLIST_ACCESS", object)',
    provider: EntityToDtoStateProvider::class,
    processor: DtoToEntityStateProcessor::class,
    stateOptions: new Options(entityClass: Wishlist::class)
)]
class WishlistApi
{

    #[ApiProperty(readable: false, writable: false, identifier: true)]
    public ?int $id                       = null;

    #[Assert\NotBlank]
    public ?UserApi $ownedBy              = null;

    #[Assert\NotBlank]
    public ?ProductApi $product           = null;

    public ?\DateTimeImmutable $createdAt = null;

}