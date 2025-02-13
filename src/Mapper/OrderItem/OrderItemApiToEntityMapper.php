<?php

declare(strict_types = 1);

namespace App\Mapper\OrderItem;

use App\ApiResource\OrderItem\OrderItemApi;
use App\ApiResource\Product\ProductApi;
use App\Entity\Order;
use App\Entity\OrderItem;
use App\Entity\Product;
use App\Repository\OrderItemRepository;
use Symfonycasts\MicroMapper\AsMapper;
use Symfonycasts\MicroMapper\MapperInterface;
use Symfonycasts\MicroMapper\MicroMapperInterface;

#[AsMapper(from: OrderItemApi::class, to: OrderItem::class)]
class OrderItemApiToEntityMapper implements MapperInterface
{

    public function __construct(
        private readonly OrderItemRepository $repository,
        private readonly MicroMapperInterface $microMapper,
    )
    {
    }

    public function load(object $from, string $toClass, array $context): object
    {
        $dto = $from;
        assert($dto instanceof OrderItemApi);

        $entity = $dto->id ? $this->repository->find($dto->id) : new OrderItem();

        if ( ! $entity ) {
            throw new \Exception(
                sprintf('OrderItem with id "%d" not found', $dto->id)
            );
        }

        return $entity;

    }

    public function populate(object $from, object $to, array $context): object
    {
        $dto    = $from;
        $entity = $to;
        assert($dto instanceof OrderItemApi);
        assert($entity instanceof OrderItem);

        $entity->setOrder (
            $this->microMapper->map($dto->order, Order::class, [
                MicroMapperInterface::MAX_DEPTH => 0,
            ])
        );
        $entity->setProduct(
            $this->microMapper->map($dto->product, Product::class, [
                MicroMapperInterface::MAX_DEPTH => 0,
            ])
        );
        $entity->setQuantity($dto->quantity);
        $entity->setUnitPrice($dto->unitPrice);
        $entity->setTotalPrice($dto->totalPrice);
        $entity->setUnitPriceAfterDiscount($dto->unitPriceAfterDiscount);

        return $entity;
    }
}