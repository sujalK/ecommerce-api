<?php

declare(strict_types = 1);

namespace App\Mapper\Coupon;

use App\ApiResource\Coupon\CouponApi;
use App\Entity\Coupon;
use App\Repository\CouponRepository;
use Symfonycasts\MicroMapper\AsMapper;
use Symfonycasts\MicroMapper\MapperInterface;

#[AsMapper(from: CouponApi::class, to: Coupon::class)]
class CouponApiToEntityMapper implements MapperInterface
{

    public function __construct (
        private readonly CouponRepository $repository,
    )
    {
    }

    public function load(object $from, string $toClass, array $context): object
    {
        $dto = $from;
        assert($dto instanceof CouponApi);

        $entity = $dto->id ? $this->repository->find($dto->id) : new Coupon();

        if ( ! $entity ) {
            throw new \Exception (
                \sprintf('Coupon with id "%d" not found', $dto->id)
            );
        }

        return $entity;
    }

    public function populate(object $from, object $to, array $context): object
    {
        $dto    = $from;
        $entity = $to;
        assert($dto instanceof CouponApi);
        assert($entity instanceof Coupon);

        $entity->setCode($dto->code);
        $entity->setDiscountType($dto->discountType);
        $entity->setDiscountValue($dto->discountValue);
        $entity->setMaxDiscountAmountForPercentage($dto->maxDiscountAmountForPercentage);
        $entity->setAppliesTo($dto->appliesTo);
        if ($dto->minimumCartValue) {
            $entity->setMinimumCartValue($dto->minimumCartValue);
        }
        $entity->setStartDate($dto->startDate);
        $entity->setEndDate($dto->endDate);
        $entity->setUsageLimit($dto->usageLimit);
        $entity->setSingleUserLimit($dto->singleUserLimit);
        $entity->setDescription($dto->description);
        $entity->setActive($dto->isActive);

        return $entity;
    }
}