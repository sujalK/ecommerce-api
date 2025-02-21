<?php

declare(strict_types = 1);

namespace App\ApiResource\ShippingMethod;

use ApiPlatform\Doctrine\Orm\State\Options;
use ApiPlatform\Metadata\ApiProperty;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Delete;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Patch;
use ApiPlatform\Metadata\Post;
use App\Entity\ShippingMethod;
use App\State\DtoToEntityStateProcessor;
use App\State\EntityToDtoStateProvider;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Validator\Constraints as Assert;

#[ApiResource(
    shortName: 'ShippingMethod',
    description: 'Shipping Method',
    operations: [
        new Get(),
        new GetCollection(),
        new Post(
            validationContext: ['groups' => ['Default', 'postValidation']]
        ),
        new Patch(
            validationContext: ['groups' => ['Default', 'patchValidation']]
        ),
        new Delete(),
    ],
    paginationItemsPerPage: 10,
    security: 'is_granted("ROLE_ADMIN")',
    provider: EntityToDtoStateProvider::class,
    processor: DtoToEntityStateProcessor::class,
    stateOptions: new Options(entityClass: ShippingMethod::class),
)]
#[UniqueEntity(fields: ['name'], message: 'The shipping method "name" must be unique.', entityClass: ShippingMethod::class)]
class ShippingMethodApi
{
    #[ApiProperty(readable: true, writable: false, identifier: true)]
    public ?int $id                       = null;

    /* can be "Standard" or "Express" */
    #[Assert\NotBlank(groups: ['postValidation'])]
    #[Assert\Length (
        min: 1,
        max: 255,
        maxMessage: 'You have reached max length for this shipping method "name".',
    )]
    public string $name                   = 'standard';

    /* $20 for "Standard" and $40 for "Express" */
    #[Assert\NotBlank(groups: ['postValidation'])]
    public ?string $cost                  = null;

    #[Assert\NotBlank(groups: ['postValidation'])]
    public ?string $estimatedDeliveryTime = null;
}