<?php

declare(strict_types = 1);

namespace App\Mapper\Inventory;

use App\ApiResource\Inventory\InventoryApi;
use App\Entity\Inventory;
use App\Entity\Product;
use App\Repository\InventoryRepository;
use Symfonycasts\MicroMapper\AsMapper;
use Symfonycasts\MicroMapper\MapperInterface;
use Symfonycasts\MicroMapper\MicroMapperInterface;

#[AsMapper(from: InventoryApi::class, to: Inventory::class)]
class InventoryApiToEntityMapper implements MapperInterface
{

    public function __construct (
        private readonly InventoryRepository $repository,
        private readonly MicroMapperInterface $microMapper,
    )
    {
    }

    public function load(object $from, string $toClass, array $context): object
    {

        $dto = $from;
        assert($dto instanceof InventoryApi);

        $entity = $dto->id ? $this->repository->find($dto->id) : new Inventory();

        if ( ! $entity ) {
            throw new \Exception(sprintf('The Inventory with id "%d" does not exist', $dto->id));
        }

        return $entity;
    }

    public function populate(object $from, object $to, array $context): object
    {
        $dto    = $from;
        $entity = $to;
        assert($dto instanceof InventoryApi);
        assert($entity instanceof Inventory);

        $entity->setQuantityInStock($dto->quantityInStock);
        $entity->setQuantitySold($dto->quantitySold);
        $entity->setQuantityBackOrdered($dto->quantityBackOrdered);
        $entity->setProduct (
            $this->microMapper->map($dto->product, Product::class, [
                MicroMapperInterface::MAX_DEPTH => 0,
            ])
        );

        return $entity;
    }
}