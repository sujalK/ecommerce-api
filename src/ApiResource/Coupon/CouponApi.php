<?php

declare(strict_types = 1);

namespace App\ApiResource\Coupon;

use ApiPlatform\Doctrine\Orm\State\Options;
use ApiPlatform\Metadata\ApiProperty;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Delete;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Patch;
use ApiPlatform\Metadata\Post;
use App\Entity\Coupon;
use App\State\CouponStateProcessor;
use App\State\DtoToEntityStateProcessor;
use App\State\EntityToDtoStateProvider;
use Symfony\Component\Validator\Constraints as Assert;

#[ApiResource (
    shortName: 'Coupon',
    description: 'Coupon for the product',
    operations: [
        new Get(
            security: 'is_granted("ROLE_COUPON_GET")',
        ),
        new GetCollection(
            security: 'is_granted("ROLE_COUPON_COLLECTION_GET")',
        ),
        new Post(
            security: 'is_granted("ROLE_COUPON_CREATE")',
            validationContext: ['groups' => ['Default', 'postValidation']]
        ),
        new Patch(
            security: 'is_granted("ROLE_COUPON_EDIT")',
        ),
        new Delete(
            security: 'is_granted("ROLE_COUPON_DELETE")',
        ),
    ],
    provider: EntityToDtoStateProvider::class,
    processor: CouponStateProcessor::class,
    stateOptions: new Options(entityClass: Coupon::class)
)]
class CouponApi
{
    #[ApiProperty(readable: false, writable: false, identifier: true)]
    public ?int $id                                = null;

    #[Assert\NotBlank(groups: ['postValidation'])]
    #[Assert\Regex(pattern: '/^[a-zA-Z0-9]+$/')]
    public ?string $code                           = null;

    #[Assert\NotBlank(groups: ['postValidation'])]
    public ?string $discountType                   = null;

    #[Assert\NotBlank(groups: ['postValidation'])]
    public ?string $discountValue                  = null;

    #[Assert\NotBlank(groups: ['postValidation'])]
    public ?string $maxDiscountAmountForPercentage = null;

    #[Assert\NotBlank(groups: ['postValidation'])]
    public ?array $appliesTo                       = [];

    #[Assert\NotBlank(groups: ['postValidation'])]
    public ?string $minimumCartValue               = null;

    #[Assert\NotBlank(groups: ['postValidation'])]
    public ?\DateTimeImmutable $startDate          = null;

    #[Assert\NotBlank(groups: ['postValidation'])]
    public ?\DateTimeImmutable $endDate            = null;

    #[Assert\NotBlank(groups: ['postValidation'])]
    #[Assert\Regex(
        pattern: '/^[0-9]+$/',
        message: 'The usage limit must be a number.',
    )]
    public ?int  $usageLimit                       = null;

    #[Assert\NotBlank(groups: ['postValidation'])]
    #[Assert\Regex(
        pattern: '/^[0-9]+$/',
        message: 'The single user usage limit must be a number.',
    )]
    public ?int $singleUserLimit                   = null;

    #[Assert\NotBlank(groups: ['postValidation'])]
    #[Assert\Regex(
        pattern: '/^[a-zA-Z0-9 \.\-]+$/',
    )]
    public ?string $description                    = null;

    #[Assert\NotBlank(groups: ['postValidation'])]
    public bool $isActive                          = true;

}