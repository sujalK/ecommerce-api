<?php

declare(strict_types = 1);

namespace App\ApiResource\Product;

use ApiPlatform\Doctrine\Orm\State\Options;
use ApiPlatform\Metadata\ApiProperty;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Delete;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Patch;
use ApiPlatform\Metadata\Post;
use App\ApiResource\CartItem\CartItemApi;
use App\ApiResource\Inventory\InventoryApi;
use App\ApiResource\ProductCategory\ProductCategoryApi;
use App\Entity\Product;
use App\State\DtoToEntityStateProcessor;
use App\State\EntityToDtoStateProvider;

#[ApiResource (
    shortName: 'Product',
    operations: [
        new Get(),
        new GetCollection(),
        new Post (
            security: 'is_granted("CREATE", object)'
        ),
        new Patch (
            security: 'is_granted("UPDATE", object)'
        ),
        new Delete (
            security: 'is_granted("DELETE", object)'
        ),
    ],
    paginationItemsPerPage: 10,
    provider: EntityToDtoStateProvider::class,
    processor: DtoToEntityStateProcessor::class,
    stateOptions: new Options(entityClass: Product::class),
)]
class ProductApi
{

    #[ApiProperty(readable: false, writable: false, identifier: true)]
    public ?int $id                       = null;

    public ?string $name                  = null;

    public ?string $description           = null;

    public ?string $price                 = null;

    public ?string $image_url             = null;

    public ?ProductCategoryApi $category  = null;

    public ?\DateTimeImmutable $createdAt = null;

    public ?\DateTimeImmutable $updatedAt = null;

    public ?bool $isActive                = null;

    /**
     * @var CartItemApi[]
     */
    public ?array $cartItems              = null;

    /**
     * @var InventoryApi[]
     */
    public ?array $inventories            = null;

}