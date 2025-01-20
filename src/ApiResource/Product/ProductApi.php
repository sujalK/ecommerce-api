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
use App\Controller\CreateProductController;
use App\Entity\Product;
use App\State\DeleteProductStateProcessor;
use App\State\DtoToEntityStateProcessor;
use App\State\EntityToDtoStateProvider;
use App\State\ProductInfoStateProcessor;
use App\Validator\IsBoolean;
use Symfony\Component\Serializer\Attribute\Ignore;
use Symfony\Component\Validator\Constraints as Assert;

#[ApiResource (
    shortName: 'Product',
    operations: [
        new Get (
            security: 'is_granted("PUBLIC_ACCESS")',
        ),
        new GetCollection (
            security: 'is_granted("PUBLIC_ACCESS")',
        ),
        new Post (
            /** Security is already included in this controller for 'admin only' access */
            controller: CreateProductController::class,
        ),
        new Patch (
            security: 'is_granted("PATCH", object)',
            processor: ProductInfoStateProcessor::class,
        ),
        new Delete (
            security: 'is_granted("DELETE", object)',
            processor: DeleteProductStateProcessor::class,
        ),
    ],
    paginationItemsPerPage: 10,
    provider: EntityToDtoStateProvider::class,
    processor: DtoToEntityStateProcessor::class,
    stateOptions: new Options(entityClass: Product::class),
)]
class ProductApi
{
    #[ApiProperty(readable: true, writable: false, identifier: true)]
    public ?int $id                       = null;

    #[Assert\NotBlank]
    #[Assert\Length(min: 2, max: 100)]
    #[Assert\Regex(
        pattern: '/^[a-zA-Z0-9 ]+$/',
        message: 'The product name can only contain letters, numbers, and spaces.'
    )]
    public ?string $name                  = null;

    #[Assert\NotBlank]
    #[Assert\Length(min: 2, max: 10000)]
    #[Assert\Regex(
        pattern: '/^[a-zA-Z0-9 ]+$/',
        message: 'The description can only contain letters, numbers, and spaces.'
    )]
    public ?string $description           = null;

    #[Assert\NotBlank]
    #[Assert\Regex (
        pattern: '/^\d{1,10}(\.\d{1,2})?$/',
        message: "Invalid price"
    )]
    public ?string $price                 = null;

    #[Assert\NotBlank]
    public ?ProductCategoryApi $category  = null;

    #[ApiProperty(readable: false, writable: false)]
    public ?\DateTimeImmutable $createdAt = null;

    #[ApiProperty(readable: true, writable: false)]
    public ?\DateTimeImmutable $updatedAt = null;

    #[IsBoolean]
    #[Assert\Type(type: 'bool')]
    #[Assert\Choice(
        choices: [true, false],
        message: 'The value of "isActive" must either be true/false'
    )]
    public ?bool $isActive                = null;

    /**
     * @var CartItemApi[]
     */
    //  public ?array $cartItems              = null;

    /**
     * @var InventoryApi[]
     */
    // public ?array $inventories            = null;

    /**
     * Making both the s3FileName and the originalFileName non-readable or non-writable
     * because we're setting both of those during POST operation
     */

    #[ApiProperty(readable: false, writable: false)]
    #[Ignore]
    public ?string $s3FileName            = null;

    #[ApiProperty(readable: false, writable: false)]
    #[Ignore]
    public ?string $originalFileName      = null;

    /** Custom property that is returned during request using custom state provider */
    public ?string $productImage          = null;

}