<?php

declare(strict_types = 1);

namespace App\ApiResource\Inventory;

use ApiPlatform\Doctrine\Orm\State\Options;
use ApiPlatform\Metadata\ApiProperty;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Delete;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Patch;
use ApiPlatform\Metadata\Post;
use App\ApiResource\Product\ProductApi;
use App\Entity\Inventory;
use App\State\EntityToDtoStateProvider;
use App\State\InventoryStateProcessor;
use App\Validator\IsUniqueProductInInventory;
use Symfony\Component\Validator\Constraints as Assert;

#[ApiResource (
    shortName: 'Inventory',
    description: 'Product Inventory',
    operations: [
        new Get(),
        new GetCollection(),
        new Post (
            validationContext: ['groups' => ['Default', 'postValidation']],
        ),
        new Patch(),
        new Delete(),
    ],
    paginationItemsPerPage: 10,
    // Allow only Admin user to access this resource
    security: 'is_granted("ROLE_ADMIN")',
    provider: EntityToDtoStateProvider::class,
    // processor: DtoToEntityStateProcessor::class,
    processor: InventoryStateProcessor::class,
    stateOptions: new Options(entityClass: Inventory::class),
)]
class InventoryApi
{
    #[ApiProperty(readable: true, writable: false, identifier: true)]
    public ?int $id                  = null;

    #[Assert\NotBlank(groups: ['postValidation'])]
    #[Assert\GreaterThan(0)]
    #[Assert\Positive]
    #[Assert\Regex(pattern: '/^[0-9]+$/', message: 'Please make sure to enter a valid quantity.')]
    public ?int $quantityInStock     = null;

    #[Assert\Regex (
        pattern: '/^[0-9]+$/',
        message: 'Please make sure to enter a valid quantity.',
    )]
    #[ApiProperty(readable: true, writable: false)]
    public int $quantitySold         = 0; // This 0 is set during POST request only, not on PATCH

    #[Assert\Regex(
        pattern: '/^[0-9]+$/',
        message: 'Please make sure to enter a valid quantity back ordered.',
    )]
    public ?int $quantityBackOrdered = null;

    #[IsUniqueProductInInventory(groups: ['postValidation'])]
    #[Assert\NotBlank(groups: ['postValidation'])]
    public ?ProductApi $product      = null;
}