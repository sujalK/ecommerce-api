<?php

declare(strict_types = 1);

namespace App\Mapper\Cart;

use App\ApiResource\Cart\CartApi;
use App\ApiResource\CartItem\CartItemApi;
use App\ApiResource\User\UserApi;
use App\Entity\Cart;
use App\Entity\CartItem;
use Symfony\Component\PropertyAccess\PropertyAccessorInterface;
use Symfonycasts\MicroMapper\AsMapper;
use Symfonycasts\MicroMapper\MapperInterface;
use Symfonycasts\MicroMapper\MicroMapperInterface;

#[AsMapper(from: Cart::class, to: CartApi::class)]
class CartEntityToApiMapper implements MapperInterface
{

    public function __construct (
        private readonly MicroMapperInterface $microMapper,
        private readonly PropertyAccessorInterface $propertyAccessor,
    )
    {
    }

    public function load(object $from, string $toClass, array $context): object
    {
        $entity = $from;
        assert($entity instanceof Cart);

        $dto     = new CartApi();
        $dto->id = $entity->getId();

        return $dto;
    }

    public function populate(object $from, object $to, array $context): object
    {
        $entity = $from;
        $dto    = $to;
        assert($entity instanceof Cart);
        assert($dto instanceof CartApi);

        $dto->owner = $this->microMapper->map($entity->getOwner(), UserApi::class, [
            MicroMapperInterface::MAX_DEPTH => 0,
        ]);
        $dto->status     = $entity->getStatus();
        $dto->createdAt  = $entity->getCreatedAt();
        $dto->updatedAt  = $entity->getUpdatedAt();
        $dto->couponCode = $entity->getCouponCode();
        $dto->totalPrice = $entity->getTotalPrice();

        $microMapper     = $this->microMapper;
        $dto->cartItems  = array_map(static fn (CartItem $cartItem) => $microMapper->map($cartItem, CartItemApi::class, [ MicroMapperInterface::MAX_DEPTH => 0, ]), $entity->getCartItems()->getValues());
        
        return $dto;
    }
}