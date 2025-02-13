<?php

declare(strict_types = 1);

namespace App\ApiResource\ShippingMethod;

use ApiPlatform\Doctrine\Orm\State\Options;
use ApiPlatform\Metadata\ApiProperty;
use ApiPlatform\Metadata\ApiResource;
use App\Entity\ShippingMethod;
use App\State\DtoToEntityStateProcessor;
use App\State\EntityToDtoStateProvider;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Validator\Constraints as Assert;

#[ApiResource(
    shortName: 'ShippingMethod',
    description: 'Shipping Method',
    paginationItemsPerPage: 10,
    security: 'is_granted("ROLE_ADMIN")',
    provider: EntityToDtoStateProvider::class,
    processor: DtoToEntityStateProcessor::class,
    stateOptions: new Options(entityClass: ShippingMethod::class),
)]
#[UniqueEntity(fields: ['name'], message: 'The shipping method "name" must be unique.', entityClass: ShippingMethod::class)]
class ShippingMethodApi
{
    #[ApiProperty(readable: false, writable: false, identifier: true)]
    public ?int $id                       = null;

    /* can be "Standard" or "Express" */
    #[Assert\NotBlank]
    public ?string $name                  = null;

    /* $20 for "Standard" and $40 for "Express" */
    #[Assert\NotBlank]
    public ?string $cost                  = null;

    /*  */
    #[Assert\NotBlank]
    public ?string $estimatedDeliveryTime = null;
}