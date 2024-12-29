<?php

declare(strict_types = 1);

namespace App\Mapper\ShippingAddress;

use App\ApiResource\Order\OrderApi;
use App\ApiResource\ShippingAddress\ShippingAddressApi;
use App\ApiResource\User\UserApi;
use App\Entity\Order;
use App\Entity\ShippingAddress;
use Symfonycasts\MicroMapper\AsMapper;
use Symfonycasts\MicroMapper\MapperInterface;
use Symfonycasts\MicroMapper\MicroMapperInterface;

#[AsMapper(from: ShippingAddress::class, to: ShippingAddressApi::class)]
class ShippingAddressEntityToApiMapper implements MapperInterface
{

    public function __construct (
        private readonly MicroMapperInterface $microMapper,
    )
    {
    }

    public function load(object $from, string $toClass, array $context): object
    {
        $entity = $from;
        assert($entity instanceof ShippingAddress);

        $dto     = new ShippingAddressApi();
        $dto->id = $entity->getId();

        return $dto;
    }

    public function populate(object $from, object $to, array $context): object
    {
        $entity = $from;
        $dto    = $to;
        assert($entity instanceof ShippingAddress);
        assert($dto instanceof ShippingAddressApi);

        $dto->owner = $this->microMapper->map($entity->getOwner(), UserApi::class, [
            MicroMapperInterface::MAX_DEPTH => 0,
        ]);
        $dto->addressLine1 = $entity->getAddressLine1();
        $dto->addressLine2 = $entity->getAddressLine2();
        $dto->city         = $entity->getCity();
        $dto->state        = $entity->getState();
        $dto->postalCode   = $entity->getPostalCode();
        $dto->country      = $entity->getCountry();
        $dto->phoneNumber  = $entity->getPhoneNumber();
        $dto->createdAt    = $entity->getCreatedAt();
        $dto->updatedAt    = $entity->getUpdatedAt();

        $dto->orders       = array_map(static fn (Order $order) => $this->microMapper->map($order, OrderApi::class, [ MicroMapperInterface::MAX_DEPTH => 0, ]), $entity->getOrders()->getValues());

        return $dto;
    }
}