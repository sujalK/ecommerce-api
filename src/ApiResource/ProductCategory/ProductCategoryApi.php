<?php

declare(strict_types = 1);

namespace App\ApiResource\ProductCategory;

use ApiPlatform\Doctrine\Orm\State\Options;
use ApiPlatform\Metadata\ApiProperty;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Delete;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Patch;
use ApiPlatform\Metadata\Post;
use App\ApiResource\Product\ProductApi;
use App\Entity\ProductCategory;
use App\State\DtoToEntityStateProcessor;
use App\State\EntityToDtoStateProvider;
use App\State\ProductCategoryStateProcessor;
use App\Validator\IsUniqueCategory;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Constraints\NotBlank;

#[ApiResource (
    shortName: 'Category',
    description: 'Product category',
    operations: [
        new Get(),
        new GetCollection(),
        new Post(
            validationContext: ['groups' => ['Default', 'postValidation']]
        ),
        new Patch(),
        new Delete(),
    ],
    security: 'is_granted("ROLE_ADMIN")',
    provider: EntityToDtoStateProvider::class,
    processor: ProductCategoryStateProcessor::class,
    stateOptions: new Options(entityClass: ProductCategory::class)
)]
#[UniqueEntity(
    fields: ['categoryName'],
    message: "This category name is already in use.",
    entityClass: ProductCategory::class,
)]
class ProductCategoryApi
{

    #[ApiProperty(readable: true, writable: false, identifier: true)]
    public ?int $id              = null;

    #[NotBlank(groups: ['postValidation'])]
    #[Assert\Regex(
        pattern: '/^[a-zA-Z0-9 \-\.]+$/',
        message: 'Please make sure to enter a valid category name.',
    )]
    #[IsUniqueCategory(groups: ['postValidation'])]
    public ?string $categoryName = null;

    #[NotBlank(groups: ['postValidation'])]
    #[Assert\Length(
        min: 1,
        max: 255,
        maxMessage: 'Description length exceeded.'
    )]
    public ?string $description  = null;

//    /**
//     * @var ProductApi[]
//     */
    // public ?array $products      = null;

}