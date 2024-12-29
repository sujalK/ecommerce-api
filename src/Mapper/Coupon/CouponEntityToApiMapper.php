<?php

declare(strict_types = 1);

namespace App\Mapper\Coupon;

use App\ApiResource\Coupon\CouponApi;
use App\Entity\Coupon;
use Symfonycasts\MicroMapper\AsMapper;
use Symfonycasts\MicroMapper\MapperInterface;

#[AsMapper(from: Coupon::class, to: CouponApi::class)]
class CouponEntityToApiMapper implements MapperInterface
{

    public function load(object $from, string $toClass, array $context): object
    {
        $entity = $from;
        assert($entity instanceof Coupon);

        $dto = new CouponApi();
        $dto->id = $entity->getId();

        return $dto;
    }

    public function populate(object $from, object $to, array $context): object
    {
        $entity = $from;
        $dto    = $to;
        assert($entity instanceof Coupon);
        assert($dto instanceof CouponApi);

        $dto->code                           = $entity->getCode();
        $dto->discountType                   = $entity->getDiscountType();
        $dto->discountValue                  = $entity->getDiscountValue();
        $dto->maxDiscountAmountForPercentage = $entity->getMaxDiscountAmountForPercentage();
        $dto->appliesTo                      = $entity->getAppliesTo();
        $dto->startDate                      = $entity->getStartDate();
        $dto->endDate                        = $entity->getEndDate();
        $dto->usageLimit                     = $entity->getUsageLimit();
        $dto->singleUserLimit                = $entity->getSingleUserLimit();
        $dto->description                    = $entity->getDescription();
        $dto->isActive                       = $entity->isActive();

        return $dto;
    }
}