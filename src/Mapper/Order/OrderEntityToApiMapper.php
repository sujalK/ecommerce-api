<?php

declare(strict_types = 1);

namespace App\Mapper\Order;

use App\ApiResource\Order\OrderApi;
use App\ApiResource\ShippingAddress\ShippingAddressApi;
use App\ApiResource\ShippingMethod\ShippingMethodApi;
use App\ApiResource\User\UserApi;
use App\Entity\Order;
use Symfonycasts\MicroMapper\AsMapper;
use Symfonycasts\MicroMapper\MapperInterface;
use Symfonycasts\MicroMapper\MicroMapperInterface;

#[AsMapper(from: Order::class, to: OrderApi::class)]
class OrderEntityToApiMapper implements MapperInterface
{

    public function __construct(
        private readonly MicroMapperInterface $microMapper,
    )
    {
    }

    public function load(object $from, string $toClass, array $context): object
    {
        $entity = $from;
        assert($entity instanceof Order);

        $dto     = new OrderApi();
        $dto->id = $entity->getId();

        return $dto;
    }

    public function populate(object $from, object $to, array $context): object
    {
        $entity = $from;
        $dto    = $to;
        assert($entity instanceof Order);
        assert($dto instanceof OrderApi);

        $dto->ownedBy         = $this->microMapper->map($entity->getOwnedBy(), UserApi::class, [
            MicroMapperInterface::MAX_DEPTH => 0,
        ]);
        $dto->totalPrice      = $entity->getTotalPrice();
        $dto->status          = $entity->getStatus();
        $dto->shippingAddress = $this->microMapper->map($entity->getShippingAddress(), ShippingAddressApi::class, [
            MicroMapperInterface::MAX_DEPTH => 0,
        ]);
        $dto->paymentStatus   = $entity->getPaymentStatus();
        $dto->couponCode      = $entity->getCouponCode();
        $dto->createdAt       = $entity->getCreatedAt();
        $dto->updatedAt       = $entity->getUpdatedAt();
        $dto->shippingMethod  = $this->microMapper->map($entity->getShippingMethod(), ShippingMethodApi::class, [
            MicroMapperInterface::MAX_DEPTH => 0,
        ]);
        $dto->currency        = $entity->getCurrency();
        
        return $dto;
    }
}