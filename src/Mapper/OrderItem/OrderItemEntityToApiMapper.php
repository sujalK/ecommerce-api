<?php

declare(strict_types = 1);

namespace App\Mapper\OrderItem;

use App\ApiResource\Order\OrderApi;
use App\ApiResource\OrderItem\OrderItemApi;
use App\ApiResource\Product\ProductApi;
use App\Entity\OrderItem;
use Symfonycasts\MicroMapper\AsMapper;
use Symfonycasts\MicroMapper\MapperInterface;
use Symfonycasts\MicroMapper\MicroMapperInterface;

#[AsMapper(from: OrderItem::class, to: OrderItemApi::class)]
class OrderItemEntityToApiMapper implements MapperInterface
{

    public function __construct (
        private readonly MicroMapperInterface $microMapper,
    )
    {
    }

    public function load(object $from, string $toClass, array $context): object
    {
        $entity = $from;
        assert($entity instanceof OrderItem);

        $dto     = new OrderItemApi();
        $dto->id = $entity->getId();

        return $dto;
    }

    public function populate(object $from, object $to, array $context): object
    {
        $entity = $from;
        $dto    = $to;
        assert($entity instanceof OrderItem);
        assert($dto instanceof OrderItemApi);

        $dto->parentOrder    = $this->microMapper->map($entity->getParentOrder(), OrderApi::class, [
            MicroMapperInterface::MAX_DEPTH => 0,
        ]);
        $dto->product        = $this->microMapper->map($entity->getProduct(), ProductApi::class, [
            MicroMapperInterface::MAX_DEPTH => 0,
        ]);
        $dto->quantity       = $entity->getQuantity();
        $dto->totalPrice     = $entity->getTotalPrice();
        $dto->unitPriceAfterDiscount = $entity->getUnitPriceAfterDiscount();

        return $dto;
    }
}