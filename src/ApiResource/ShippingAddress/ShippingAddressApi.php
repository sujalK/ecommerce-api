<?php

declare(strict_types = 1);

namespace App\ApiResource\ShippingAddress;

use ApiPlatform\Doctrine\Orm\State\Options;
use ApiPlatform\Metadata\ApiProperty;
use ApiPlatform\Metadata\ApiResource;
use App\ApiResource\Order\OrderApi;
use App\ApiResource\User\UserApi;
use App\Entity\ShippingAddress;
use App\State\DtoToEntityStateProcessor;
use App\State\EntityToDtoStateProvider;

#[ApiResource(
    shortName: 'ShippingAddress',
    description: 'Shipping Address',
    provider: EntityToDtoStateProvider::class,
    processor: DtoToEntityStateProcessor::class,
    stateOptions: new Options(entityClass: ShippingAddress::class)
)]
class ShippingAddressApi
{

    #[ApiProperty(readable: false, writable: false, identifier: true)]
    public ?int $id                       = null;

    public ?UserApi $owner                = null;

    public ?string $addressLine1          = null;

    public ?string $addressLine2          = null;

    public ?string $city                  = null;

    public ?string $state                 = null;

    public ?string $postalCode            = null;

    public ?string $country               = null;

    public ?string $phoneNumber           = null;

    public ?\DateTimeImmutable $createdAt = null;

    public ?\DateTimeImmutable $updatedAt = null;

    /**
     * @var OrderApi[]
     */
    public ?array $orders                 = null;

}