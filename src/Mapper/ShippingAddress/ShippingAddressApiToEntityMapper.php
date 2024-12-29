<?php

declare(strict_types = 1);

namespace App\Mapper\ShippingAddress;

use App\ApiResource\Order\OrderApi;
use App\ApiResource\ShippingAddress\ShippingAddressApi;
use App\Entity\Order;
use App\Entity\ShippingAddress;
use App\Entity\User;
use App\Repository\ShippingAddressRepository;
use Symfony\Component\PropertyAccess\PropertyAccessorInterface;
use Symfonycasts\MicroMapper\AsMapper;
use Symfonycasts\MicroMapper\MapperInterface;
use Symfonycasts\MicroMapper\MicroMapperInterface;

#[AsMapper(from: ShippingAddressApi::class, to: ShippingAddress::class)]
class ShippingAddressApiToEntityMapper implements MapperInterface
{

    public function __construct (
        private readonly ShippingAddressRepository $repository,
        private readonly MicroMapperInterface $microMapper,
        private readonly PropertyAccessorInterface $propertyAccessor,
    )
    {
    }

    public function load(object $from, string $toClass, array $context): object
    {
        $dto = $from;
        assert($dto instanceof ShippingAddressApi);

        $entity = $dto->id ? $this->repository->find($dto->id) : new ShippingAddress();

        if ( ! $entity ) {
            throw new \Exception (
                \sprintf('Shipping address with id "%d" not found', $dto->id)
            );
        }

        return $entity;
    }

    public function populate(object $from, object $to, array $context): object
    {
        $dto    = $from;
        $entity = $to;
        assert($dto instanceof ShippingAddressApi);
        assert($entity instanceof ShippingAddress);

        $entity->setOwner (
            $this->microMapper->map($dto->owner, User::class, [
                MicroMapperInterface::MAX_DEPTH => 0,
            ])
        );
        $entity->setAddressLine1($dto->addressLine1);
        $entity->setAddressLine2($dto->addressLine2);
        $entity->setCity($dto->city);
        $entity->setState($dto->state);
        $entity->setPostalCode($dto->postalCode);
        $entity->setCountry($dto->country);
        $entity->setPhoneNumber($dto->phoneNumber);
        $entity->setCreatedAt($dto->createdAt);
        $entity->setUpdatedAt($dto->updatedAt);

        // OrderApi[] -> Order[]
        $microMapper   = $this->microMapper;
        $orderEntities = array_map (static fn(OrderApi $orderApi) => $microMapper->map ($orderApi, Order::class, [ MicroMapperInterface::MAX_DEPTH => 0, ]), $dto->orders);

        // set values
        $this->propertyAccessor->setValue($entity, 'orders', $orderEntities);

        return $entity;
    }
}