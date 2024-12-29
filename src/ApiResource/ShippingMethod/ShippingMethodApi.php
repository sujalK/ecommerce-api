<?php

declare(strict_types = 1);

namespace App\ApiResource\ShippingMethod;

use ApiPlatform\Doctrine\Orm\State\Options;
use ApiPlatform\Metadata\ApiProperty;
use ApiPlatform\Metadata\ApiResource;
use App\Entity\ShippingMethod;
use App\State\DtoToEntityStateProcessor;
use App\State\EntityToDtoStateProvider;
use Symfony\Component\Validator\Constraints as Assert;

#[ApiResource(
    shortName: 'ShippingMethod',
    description: 'Shipping Method',
    provider: EntityToDtoStateProvider::class,
    processor: DtoToEntityStateProcessor::class,
    stateOptions: new Options(entityClass: ShippingMethod::class),
)]
class ShippingMethodApi
{
    #[ApiProperty(readable: false, writable: false, identifier: true)]
    public ?int $id                       = null;

    #[Assert\NotBlank]
    public ?string $name                  = null;

    #[Assert\NotBlank]
    public ?string $cost                  = null;

    #[Assert\NotBlank]
    public ?string $estimatedDeliveryTime = null;
}