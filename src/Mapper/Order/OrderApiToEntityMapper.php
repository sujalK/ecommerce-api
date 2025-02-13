<?php

declare(strict_types = 1);

namespace App\Mapper\Order;

use App\ApiResource\Order\OrderApi;
use App\Entity\Order;
use App\Entity\ShippingAddress;
use App\Entity\ShippingMethod;
use App\Entity\User;
use App\Repository\OrderRepository;
use Symfonycasts\MicroMapper\AsMapper;
use Symfonycasts\MicroMapper\MapperInterface;
use Symfonycasts\MicroMapper\MicroMapperInterface;

#[AsMapper(from: OrderApi::class, to: Order::class)]
class OrderApiToEntityMapper implements MapperInterface
{

    public function __construct (
        private readonly OrderRepository $repository,
        private readonly MicroMapperInterface $microMapper,
    )
    {
    }

    public function load(object $from, string $toClass, array $context): object
    {
        $dto = $from;
        assert($dto instanceof OrderApi);

        $entity = $dto->id ? $this->repository->find($dto->id) : new Order();

        if ( ! $entity ) {
            throw new \Exception(
                \sprintf('Order with id "%d" not found', $dto->id)
            );
        }

        return $entity;
    }

    public function populate(object $from, object $to, array $context): object
    {
        $dto    = $from;
        $entity = $to;
        assert($dto instanceof OrderApi);
        assert($entity instanceof Order);

        $entity->setOwnedBy (
            $this->microMapper->map($dto->ownedBy, User::class, [
                MicroMapperInterface::MAX_DEPTH => 0,
            ])
        );
        $entity->setTotalPrice($dto->totalPrice);
        $entity->setStatus($dto->status);
        $entity->setShippingAddress (
            $this->microMapper->map($dto->shippingAddress, ShippingAddress::class, [
                MicroMapperInterface::MAX_DEPTH => 0,
            ])
        );
        $entity->setPaymentStatus($dto->paymentStatus);
        $entity->setCouponCode($dto->couponCode);

        // if updatedAt is sent, then set it
        if ($dto->updatedAt) {
            $entity->setUpdatedAt(new \DateTimeImmutable());
        }

        $entity->setShippingMethod (
            $this->microMapper->map($dto->shippingMethod, ShippingMethod::class, [
                MicroMapperInterface::MAX_DEPTH => 0,
            ])
        );
        $entity->setCurrency($dto->currency);

        return $entity;
    }
}