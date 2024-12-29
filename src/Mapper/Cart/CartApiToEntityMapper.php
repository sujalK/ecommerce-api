<?php

declare(strict_types = 1);

namespace App\Mapper\Cart;

use App\ApiResource\Cart\CartApi;
use App\ApiResource\CartItem\CartItemApi;
use App\Entity\Cart;
use App\Entity\User;
use App\Repository\CartRepository;
use Symfony\Component\PropertyAccess\PropertyAccessorInterface;
use Symfonycasts\MicroMapper\AsMapper;
use Symfonycasts\MicroMapper\MapperInterface;
use Symfonycasts\MicroMapper\MicroMapperInterface;

#[AsMapper(from: CartApi::class, to: Cart::class)]
class CartApiToEntityMapper implements MapperInterface
{

    public function __construct (
        private readonly CartRepository $repository,
        private readonly MicroMapperInterface $microMapper,
        private readonly PropertyAccessorInterface $propertyAccessor,
    )
    {
    }

    public function load(object $from, string $toClass, array $context): object
    {
        $dto = $from;
        assert($dto instanceof CartApi);

        $entity = $dto->id ? $this->repository->find($dto->id) : new Cart();

        if ( ! $entity ) {
            throw new \Exception (
                sprintf('Cart with id "%d" not found', $dto->id)
            );
        }

        return $entity;
    }

    public function populate(object $from, object $to, array $context): object
    {
        $dto    = $from;
        $entity = $to;
        assert($dto instanceof CartApi);
        assert($entity instanceof Cart);

        $entity->setOwner(
            $this->microMapper->map($dto->owner, User::class, [
                MicroMapperInterface::MAX_DEPTH => 0,
            ])
        );
        $entity->setStatus($dto->status);

        // commented out because we already did this inside the Cart Entity class
        // $entity->setCreatedAt($dto->createdAt);

        if ( $dto->updatedAt ) {
            $entity->setUpdatedAt($dto->updatedAt);
        }

        $entity->setCouponCode($dto->couponCode);
        $entity->setTotalPrice($dto->totalPrice);

        /*
        // micro mapper for the mapping in the array_map() below
        $microMapper = $this->microMapper;
        // map cartItems
        $cartItems = array_map(static fn(CartItemApi $cartItemApi) => $microMapper->map($cartItemApi, Cart::class, [ MicroMapperInterface::MAX_DEPTH => 0, ]), $dto->cartItems);

        // set cartItems
        $this->propertyAccessor->setValue($entity, 'cartItems', $cartItems);
        */
        
        return $entity;
    }
}