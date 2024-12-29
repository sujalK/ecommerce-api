<?php

declare(strict_types = 1);

namespace App\Mapper\ProductCategory;

use App\ApiResource\Product\ProductApi;
use App\ApiResource\ProductCategory\ProductCategoryApi;
use App\Entity\Product;
use App\Entity\ProductCategory;
use App\Repository\ProductCategoryRepository;
use Symfony\Component\PropertyAccess\PropertyAccessorInterface;
use Symfonycasts\MicroMapper\AsMapper;
use Symfonycasts\MicroMapper\MapperInterface;
use Symfonycasts\MicroMapper\MicroMapperInterface;

#[AsMapper(from: ProductCategoryApi::class, to: ProductCategory::class)]
class ProductCategoryApiToEntityMapper implements MapperInterface
{

    public function __construct(
        private readonly ProductCategoryRepository $repository,
        private readonly MicroMapperInterface $microMapper,
        private readonly PropertyAccessorInterface $propertyAccessor,
    )
    {
    }

    public function load(object $from, string $toClass, array $context): object
    {
        $dto = $from;
        assert($dto instanceof ProductCategoryApi);

        $entity = $dto->id ? $this->repository->find($dto->id) : new ProductCategory();

        if ( ! $entity ) {
            throw new \Exception(
                \sprintf('ProductCategory with Id "%d" not found', $dto->id)
            );
        }

        return $entity;
    }

    public function populate(object $from, object $to, array $context): object
    {
        $dto    = $from;
        $entity = $to;

        assert($dto instanceof ProductCategoryApi);
        assert($entity instanceof ProductCategory);

        $entity->setCategoryName($dto->categoryName);
        $entity->setDescription($dto->description);

        $microMapper     = $this->microMapper;
        $productEntities = array_map(static fn(ProductApi $product) => $microMapper->map($product, Product::class, [ MicroMapperInterface::MAX_DEPTH => 0 ]), $dto->products);

        // set values to the products
        $this->propertyAccessor->setValue($entity, 'products', $productEntities);

        return $entity;
    }
}