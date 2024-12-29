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
use App\State\DtoToEntityStateProcessor;
use App\State\EntityToDtoStateProvider;

#[ApiResource (
    shortName: 'Inventory',
    description: 'Product Inventory',
    operations: [
        new Get(),
        new GetCollection(),
        new Post(),
        new Patch(),
        new Delete(),
    ],
    paginationItemsPerPage: 10,
    // Allow only Admin user to access this resource
    security: 'is_granted("ROLE_ADMIN")',
    provider: EntityToDtoStateProvider::class,
    processor: DtoToEntityStateProcessor::class,
    stateOptions: new Options(entityClass: Inventory::class),
)]
class InventoryApi
{
    #[ApiProperty(readable: false, writable: false, identifier: true)]
    public ?int $id                  = null;

    public ?int $quantityInStock     = null;

    public ?int $quantitySold        = null;

    public ?int $quantityBackOrdered = null;

    public ?ProductApi $product      = null;
}