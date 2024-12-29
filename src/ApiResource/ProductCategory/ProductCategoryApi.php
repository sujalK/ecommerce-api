<?php

declare(strict_types = 1);

namespace App\ApiResource\ProductCategory;

use ApiPlatform\Doctrine\Orm\State\Options;
use ApiPlatform\Metadata\ApiProperty;
use ApiPlatform\Metadata\ApiResource;
use App\ApiResource\Product\ProductApi;
use App\Entity\ProductCategory;
use App\State\DtoToEntityStateProcessor;
use App\State\EntityToDtoStateProvider;

#[ApiResource (
    shortName: 'ProductCategory',
    description: 'Product category',
    provider: EntityToDtoStateProvider::class,
    processor: DtoToEntityStateProcessor::class,
    stateOptions: new Options(entityClass: ProductCategory::class)
)]
class ProductCategoryApi
{

    #[ApiProperty(readable: false, writable: false, identifier: true)]
    public ?int $id              = null;

    public ?string $categoryName = null;

    public ?string $description  = null;

    /**
     * @var ProductApi[]
     */
    public ?array $products      = null;

}