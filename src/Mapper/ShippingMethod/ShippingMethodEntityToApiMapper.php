<?php

declare(strict_types = 1);

namespace App\Mapper\ShippingMethod;

use App\ApiResource\ShippingMethod\ShippingMethodApi;
use App\Entity\ShippingMethod;
use Symfonycasts\MicroMapper\AsMapper;
use Symfonycasts\MicroMapper\MapperInterface;

#[AsMapper(from: ShippingMethod::class, to: ShippingMethodApi::class)]
class ShippingMethodEntityToApiMapper implements MapperInterface
{

    public function load(object $from, string $toClass, array $context): object
    {
        $entity = $from;
        assert($entity instanceof ShippingMethod);

        $dto     = new ShippingMethodApi();
        $dto->id = $entity->getId();

        return $dto;
    }

    public function populate(object $from, object $to, array $context): object
    {
        $entity = $from;
        $dto    = $to;
        assert($entity instanceof ShippingMethod);
        assert($dto instanceof ShippingMethodApi);

        $dto->name                  = $entity->getName();
        $dto->cost                  = $entity->getCost();
        $dto->estimatedDeliveryTime = $entity->getEstimatedDeliveryTime();

        return $dto;
    }
}