<?php

declare(strict_types = 1);

namespace App\Mapper\ShippingMethod;

use App\ApiResource\ShippingMethod\ShippingMethodApi;
use App\Entity\ShippingMethod;
use App\Repository\ShippingMethodRepository;
use Symfonycasts\MicroMapper\AsMapper;
use Symfonycasts\MicroMapper\MapperInterface;

#[AsMapper(from: ShippingMethodApi::class, to: ShippingMethod::class)]
class ShippingMethodApiToEntityMapper implements MapperInterface
{

    public function __construct (
        private readonly ShippingMethodRepository $repository,
    )
    {
    }

    public function load(object $from, string $toClass, array $context): object
    {
        $dto = $from;
        assert($dto instanceof ShippingMethodApi);

        $entity = $dto->id ? $this->repository->find($dto->id) : new ShippingMethod();

        if ( ! $entity ) {
            throw new \Exception(
                \sprintf('Shipping Method with id "%d" not found', $dto->id)
            );
        }

        return $entity;
    }

    public function populate(object $from, object $to, array $context): object
    {
        $dto    = $from;
        $entity = $to;
        assert($dto instanceof ShippingMethodApi);
        assert($entity instanceof ShippingMethod);

        $entity->setName($dto->name);
        $entity->setCost($dto->cost);
        $entity->setEstimatedDeliveryTime($dto->estimatedDeliveryTime);

        return $entity;
    }
}