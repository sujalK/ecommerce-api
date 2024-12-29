<?php

declare(strict_types = 1);

namespace App\Mapper\CartItem;

use App\ApiResource\Cart\CartApi;
use App\ApiResource\CartItem\CartItemApi;
use App\ApiResource\Product\ProductApi;
use App\Entity\CartItem;
use Symfonycasts\MicroMapper\AsMapper;
use Symfonycasts\MicroMapper\MapperInterface;
use Symfonycasts\MicroMapper\MicroMapperInterface;

#[AsMapper(from: CartItem::class, to: CartItemApi::class)]
class CartItemEntityToApiMapper implements MapperInterface
{
    
    public function __construct (
        private readonly MicroMapperInterface $microMapper
    )
    {
    }

    public function load(object $from, string $toClass, array $context): object
    {
        $entity = $from;
        assert($entity instanceof CartItem);

        $dto     = new CartItemApi();
        $dto->id = $entity->getId();

        return $dto;
    }

    public function populate(object $from, object $to, array $context): object
    {
        $entity = $from;
        $dto    = $to;
        assert($entity instanceof CartItem);
        assert($dto instanceof CartItemApi);

        $dto->cart = $this->microMapper->map($entity->getCart(), CartApi::class, [
            MicroMapperInterface::MAX_DEPTH => 0,
        ]);
        $dto->product = $this->microMapper->map($entity->getProduct(), ProductApi::class, [
            MicroMapperInterface::MAX_DEPTH => 0,
        ]);

        $dto->quantity       = $entity->getQuantity();
        $dto->price_per_unit = $entity->getPricePerUnit();
        $dto->totalPrice     = $entity->getTotalPrice();

        return $dto;
    }
}