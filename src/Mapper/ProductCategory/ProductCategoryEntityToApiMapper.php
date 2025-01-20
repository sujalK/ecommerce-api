<?php

declare(strict_types = 1);

namespace App\Mapper\ProductCategory;

use App\ApiResource\Product\ProductApi;
use App\ApiResource\ProductCategory\ProductCategoryApi;
use App\Entity\Product;
use App\Entity\ProductCategory;
use Symfonycasts\MicroMapper\AsMapper;
use Symfonycasts\MicroMapper\MapperInterface;
use Symfonycasts\MicroMapper\MicroMapperInterface;

#[AsMapper(from: ProductCategory::class, to: ProductCategoryApi::class)]
class ProductCategoryEntityToApiMapper implements MapperInterface
{

    public function __construct (
        private readonly MicroMapperInterface $microMapper,
    )
    {
    }

    public function load(object $from, string $toClass, array $context): object
    {
        // load the $toClass and return it from this load() method
        $entity = $from;
        assert($entity instanceof ProductCategory);

        $dto     = new ProductCategoryApi();
        $dto->id = $entity->getId();

        return $dto;
    }

    public function populate(object $from, object $to, array $context): object
    {
        $entity = $from;
        $dto    = $to;

        assert($entity instanceof ProductCategory);
        assert($dto instanceof ProductCategoryApi);

        $dto->categoryName = $entity->getCategoryName();
        $dto->description  = $entity->getDescription();

        /** Commented out the products[] for the specific category */
        // Product[] -> ProductApi[]
//        $dto->products     = array_map(function(Product $product) {
//            return $this->microMapper->map($product, ProductApi::class, [
//                MicroMapperInterface::MAX_DEPTH => 0,
//            ]);
//        }, $entity->getProducts()->getValues());

        return $dto;
    }
}