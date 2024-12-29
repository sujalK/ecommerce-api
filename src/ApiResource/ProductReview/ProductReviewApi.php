<?php

declare(strict_types = 1);

namespace App\ApiResource\ProductReview;

use ApiPlatform\Doctrine\Orm\Filter\SearchFilter;
use ApiPlatform\Doctrine\Orm\State\Options;
use ApiPlatform\Metadata\ApiFilter;
use ApiPlatform\Metadata\ApiProperty;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Delete;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Link;
use ApiPlatform\Metadata\Patch;
use ApiPlatform\Metadata\Post;
use App\ApiResource\Product\ProductApi;
use App\ApiResource\User\UserApi;
use App\Entity\ProductReview;
use App\Entity\User;
use App\State\DtoToEntityStateProcessor;
use App\State\EntityToDtoStateProvider;
use Symfony\Component\Validator\Constraints\NotBlank;

#[ApiResource (
    shortName: 'ProductReview',
    description: 'Review of a Product',
    operations: [
        new Get (),
        new GetCollection(),
        new Patch (
            security: 'is_granted("EDIT_REVIEW", object)'
        ),
        new Post (
            security: 'is_granted("POST_REVIEW", object)'
        ),
        new Delete (
            security: 'is_granted("DELETE_REVIEW", object)'
        ),
    ],
    paginationItemsPerPage: 10,
    provider: EntityToDtoStateProvider::class,
    processor: DtoToEntityStateProcessor::class,
    stateOptions: new Options(entityClass: ProductReview::class)
)]
#[ApiResource (
    uriTemplate: '/users/{user_id}/review.{_format}',
    shortName: 'ProductReview',
    operations: [ new Get(), /* new Patch() */ ],
    uriVariables: [
        'user_id' => new Link (
            toProperty: 'owner',
            fromClass: User::class,
        )
    ],
    provider: EntityToDtoStateProvider::class,
    processor: DtoToEntityStateProcessor::class,
    stateOptions: new Options(entityClass: ProductReview::class)
)]
#[ApiFilter(SearchFilter::class, properties: ['owner.id' => 'exact'])]
class ProductReviewApi
{

    #[ApiProperty(readable: false, writable: false, identifier: true)]
    public ?int $id                       = null;

    public ?ProductApi $product           = null;

    public ?UserApi $owner                = null;

    #[NotBlank]
    public ?int $rating                   = null;

    public ?string $reviewText            = null;

    #[ApiProperty(readable: false)]
    public ?\DateTimeImmutable $createdAt = null;

    #[ApiProperty(readable: false)]
    public ?\DateTimeImmutable $updatedAt = null;

    // custom property that holds human-readable date
    public ?string $dateCreated           = null;
    public ?string $dateUpdated           = null;

}