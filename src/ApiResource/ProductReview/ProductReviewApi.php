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
use App\State\ProductReviewStateProcessor;
use App\Validator\CanPostReview;
use App\Validator\IsValidProduct;
use Symfony\Component\Validator\Constraints as Assert;

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
            security: 'is_granted("ROLE_USER")',
            validationContext: ['groups' => ['Default', 'postValidation']]
        ),
        new Delete (
            security: 'is_granted("DELETE_REVIEW", object)'
        ),
    ],
    paginationItemsPerPage: 10,
    provider: EntityToDtoStateProvider::class,
    processor: ProductReviewStateProcessor::class,
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
#[CanPostReview]
class ProductReviewApi
{

    #[ApiProperty(readable: true, writable: false, identifier: true)]
    public ?int $id                       = null;

    #[IsValidProduct]
    #[Assert\NotBlank(groups: ['postValidation'])]
    public ?ProductApi $product           = null;

    #[ApiProperty(readable: true, writable: false)]
    public ?UserApi $owner                = null;

    #[Assert\NotBlank(groups: ['postValidation'])]
    #[Assert\Range(
        notInRangeMessage: 'The rating must be between 1 and 5.',
        invalidMessage: 'The rating must be a valid number between 1 and 5.',
        min: 1,
        max: 5,
    )]
    public ?int $rating                   = null;

    #[Assert\NotBlank(groups: ['postValidation'])]
    #[Assert\Length(
        min: 5,
        max: 500,
        minMessage: 'The review is too short.',
        maxMessage: 'The review is too long.',
    )]
    public ?string $reviewText            = null;

    #[ApiProperty(readable: false, writable: false)]
    public ?\DateTimeImmutable $createdAt = null;

    #[ApiProperty(readable: false, writable: false)]
    public ?\DateTimeImmutable $updatedAt = null;

    #[ApiProperty(readable: true, writable: false)]
    public bool $isActive                = false;

    // custom property that holds human-readable date
    public ?string $dateCreated           = null;
    public ?string $dateUpdated           = null;

}