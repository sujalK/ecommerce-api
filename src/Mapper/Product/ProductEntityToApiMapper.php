<?php

declare(strict_types = 1);

namespace App\Mapper\Product;

use App\ApiResource\Cart\CartApi;
use App\ApiResource\Inventory\InventoryApi;
use App\ApiResource\Product\ProductApi;
use App\ApiResource\ProductCategory\ProductCategoryApi;
use App\Entity\Cart;
use App\Entity\Inventory;
use App\Entity\Product;
use Symfonycasts\MicroMapper\AsMapper;
use Symfonycasts\MicroMapper\MapperInterface;
use Symfonycasts\MicroMapper\MicroMapperInterface;

#[AsMapper(from: Product::class, to: ProductApi::class)]
class ProductEntityToApiMapper implements MapperInterface
{

    public function __construct (
        private readonly MicroMapperInterface $microMapper,
    )
    {
    }

    public function load(object $from, string $toClass, array $context): object
    {
        $entity = $from;
        assert($entity instanceof Product);

        $dto     = new ProductApi();
        $dto->id = $entity->getId();

        // returning the to object which is then passed to populate
        return $dto;
    }

    public function populate(object $from, object $to, array $context): object
    {
        $entity = $from;
        $dto    = $to;

        assert($entity instanceof Product);
        assert($dto instanceof ProductApi);

        $dto->name        = $entity->getName();
        $dto->description = $entity->getDescription();
        $dto->price       = $entity->getPrice();

        // TODO: GET the image url after uploading the image
        $dto->image_url   = $entity->getImageUrl();

        $dto->category    = $this->microMapper->map($entity->getCategory(), ProductCategoryApi::class, [
            MicroMapperInterface::MAX_DEPTH => 0,
        ]);

        $dto->createdAt   = $entity->getCreatedAt();
        $dto->updatedAt   = $entity->getUpdatedAt();
        $dto->isActive    = $entity->isActive();
        $dto->cartItems   = array_map(function(Cart $cart) {
            return $this->microMapper->map($cart, CartApi::class, [
                MicroMapperInterface::MAX_DEPTH => 0,
            ]);
        }, $entity->getCartItems()->getValues());

        // Inventory[] -> InventoryApi[]
        $dto->inventories = array_map(function(Inventory $inventory) {
            return $this->microMapper->map($inventory, InventoryApi::class, [
                MicroMapperInterface::MAX_DEPTH => 0,
            ]);
        }, $entity->getInventories()->getValues());

        return $dto;
    }
}