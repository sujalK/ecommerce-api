<?php

declare(strict_types = 1);

namespace App\Mapper\CartItem;

use App\ApiResource\CartItem\CartItemApi;
use App\Entity\Cart;
use App\Entity\CartItem;
use App\Entity\Product;
use App\Repository\CartItemRepository;
use Symfonycasts\MicroMapper\AsMapper;
use Symfonycasts\MicroMapper\MapperInterface;
use Symfonycasts\MicroMapper\MicroMapperInterface;

#[AsMapper(from: CartItemApi::class, to: CartItem::class)]
class CartItemApiToEntityMapper implements MapperInterface
{

    public function __construct (
        private readonly CartItemRepository $repository,
        private readonly MicroMapperInterface $microMapper,
    )
    {
    }

    public function load(object $from, string $toClass, array $context): object
    {
        $dto = $from;
        assert($dto instanceof CartItemApi);

        // Find the CartItem if it exists (for update), and for POST create new CartItem
        $entity = $dto->id ? $this->repository->find($dto->id) : new CartItem();

        if ( ! $entity ) {
            throw new \Exception (
                \sprintf('CartItem with id "%d" not found', $dto->id)
            );
        }

        return $entity;
    }

    public function populate(object $from, object $to, array $context): object
    {
        $dto    = $from;
        $entity = $to;

        assert($dto instanceof CartItemApi);
        assert($entity instanceof CartItem);

        $entity->setCart (
            $this->microMapper->map($dto->cart, Cart::class, [
                MicroMapperInterface::MAX_DEPTH => 0,
            ])
        );
        $entity->setProduct (
            $this->microMapper->map($dto->product, Product::class, [
                MicroMapperInterface::MAX_DEPTH => 0,
            ])
        );
        $entity->setQuantity($dto->quantity);
        $entity->setPricePerUnit($dto->price_per_unit);
        $entity->setTotalPrice($dto->totalPrice);

        return $entity;
    }
}