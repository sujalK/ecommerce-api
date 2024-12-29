<?php

declare(strict_types = 1);

namespace App\Mapper\Product;

use App\ApiResource\Inventory\InventoryApi;
use App\ApiResource\Product\ProductApi;
use App\Entity\CartItem;
use App\Entity\Inventory;
use App\Entity\Product;
use App\Entity\ProductCategory;
use App\Repository\ProductRepository;
use Symfony\Component\PropertyAccess\PropertyAccessorInterface;
use Symfonycasts\MicroMapper\AsMapper;
use Symfonycasts\MicroMapper\MapperInterface;
use Symfonycasts\MicroMapper\MicroMapperInterface;

#[AsMapper(from: ProductApi::class, to: Product::class)]
class ProductApiToEntityMapper implements MapperInterface
{

    public function __construct (
        private readonly ProductRepository $repository,
        private readonly MicroMapperInterface $microMapper,
        private readonly PropertyAccessorInterface $propertyAccessor,
    )
    {
    }

    public function load(object $from, string $toClass, array $context): object
    {
        $dto = $from;
        assert($dto instanceof ProductApi);

        $entity = $dto->id ? $this->repository->find($dto->id) : new Product();

        if ( ! $entity ) {
            throw new \Exception(
                \sprintf('Product Entity with id "%d" not found', $dto->id)
            );
        }

        return $entity;
    }

    public function populate(object $from, object $to, array $context): object
    {
        $dto    = $from;
        $entity = $to;
        assert($dto instanceof ProductApi);
        assert($entity instanceof Product);

        $entity->setName($dto->name);
        $entity->setDescription($dto->description);
        $entity->setPrice($dto->price);

        // WILL NEED TO IMPLEMENT USING S3
        $entity->setImageUrl($dto->image_url);

        $entity->setCategory(
            $this->microMapper->map($dto->category, ProductCategory::class, [
                MicroMapperInterface::MAX_DEPTH => 0,
            ])
        );

        if ( $dto->createdAt ) {
            $entity->setCreatedAt($dto->createdAt);
        }

        if ( $dto->id && $dto->updatedAt ) {
            $entity->setUpdatedAt($dto->updatedAt);
        }

        $entity->setIsActive($dto->isActive);

        // set cartItems
        $cartItemEntities = [];
        foreach ( $dto->cartItems as $cartItemApi ) {
            $cartItemEntities[] = $this->microMapper->map($cartItemApi, CartItem::class, [
                MicroMapperInterface::MAX_DEPTH => 0,
            ]);
        }
        $this->propertyAccessor->setValue($entity, 'cartItems', $cartItemEntities);

        // set inventories
        $inventoriesEntities = array_map(function(InventoryApi $inventoryApi) {
            return $this->microMapper->map($inventoryApi, Inventory::class, [
                MicroMapperInterface::MAX_DEPTH => 0,
            ]);
        }, $dto->inventories);
        $this->propertyAccessor->setValue($entity, 'inventories', $inventoriesEntities);

        return $entity;
    }
}