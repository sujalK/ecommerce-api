<?php

declare(strict_types = 1);

namespace App\Mapper\Inventory;

use App\ApiResource\Inventory\InventoryApi;
use App\ApiResource\Product\ProductApi;
use App\Entity\Inventory;
use Symfonycasts\MicroMapper\AsMapper;
use Symfonycasts\MicroMapper\MapperInterface;
use Symfonycasts\MicroMapper\MicroMapperInterface;

#[AsMapper(from: Inventory::class, to: InventoryApi::class)]
class InventoryEntityToApiMapper implements MapperInterface
{

    public function __construct (
        private readonly MicroMapperInterface $microMapper,
    )
    {
    }

    public function load(object $from, string $toClass, array $context): object
    {
        $entity = $from;
        assert($entity instanceof Inventory);

        $dto     = new InventoryApi();
        $dto->id = $entity->getId();

        // returning the to class
        return $dto;
    }

    public function populate(object $from, object $to, array $context): object
    {
        $entity = $from;
        $dto    = $to;
        assert($entity instanceof Inventory);
        assert($dto instanceof InventoryApi);

        // map from Inventory Entity -> InventoryApi DTO
        $dto->quantityInStock     = $entity->getQuantityInStock();
        $dto->quantitySold        = $entity->getQuantitySold();
        $dto->quantityBackOrdered = $entity->getQuantityBackOrdered();

        // Product -> ProductApi
        $dto->product = $this->microMapper->map($entity->getProduct(), ProductApi::class, [
            MicroMapperInterface::MAX_DEPTH => 0,
        ]);

        return $dto;
    }
}