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
use App\State\DtoToEntityStateProcessor;
use App\State\EntityToDtoStateProvider;

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
        ),
        new Patch(
            security: 'is_granted("ROLE_COUPON_EDIT")',
        ),
        new Delete(
            security: 'is_granted("ROLE_COUPON_DELETE")',
        ),
    ],
    provider: EntityToDtoStateProvider::class,
    processor: DtoToEntityStateProcessor::class,
    stateOptions: new Options(entityClass: Coupon::class)
)]
class CouponApi
{
    #[ApiProperty(readable: false, writable: false, identifier: true)]
    public ?int $id                                = null;

    public ?string $code                           = null;

    public ?string $discountType                   = null;

    public ?string $discountValue                  = null;

    public ?string $maxDiscountAmountForPercentage = null;

    public ?array $appliesTo                       = [];

    public ?string $minimumCartValue               = null;

    public ?\DateTimeImmutable $startDate          = null;

    public ?\DateTimeImmutable $endDate            = null;

    public ?int  $usageLimit                       = null;

    public ?int $singleUserLimit                   = null;

    public ?string $description                    = null;

    public ?bool $isActive                         = null;

}