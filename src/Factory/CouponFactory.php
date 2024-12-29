<?php

namespace App\Factory;

use App\Entity\Coupon;
use Zenstruck\Foundry\Persistence\PersistentProxyObjectFactory;

/**
 * @extends PersistentProxyObjectFactory<Coupon>
 */
final class CouponFactory extends PersistentProxyObjectFactory
{
    /**
     * @see https://symfony.com/bundles/ZenstruckFoundryBundle/current/index.html#factories-as-services
     *
     * @todo inject services if required
     */
    public function __construct()
    {
    }

    public static function class(): string
    {
        return Coupon::class;
    }

    /**
     * @see https://symfony.com/bundles/ZenstruckFoundryBundle/current/index.html#model-factories
     *
     * @todo add your default values here
     */
    protected function defaults(): array|callable
    {
        return [
            'appliesTo' => [],
            'code' => self::faker()->text(100),
            'description' => self::faker()->text(),
            'discountType' => self::faker()->text(50),
            'discountValue' => self::faker()->randomFloat(),
            'endDate' => \DateTimeImmutable::createFromMutable(self::faker()->dateTime()),
            'isActive' => self::faker()->boolean(),
            'maxDiscountAmountForPercentage' => self::faker()->randomFloat(),
            'minimumCartValue' => self::faker()->randomFloat(),
            'singleUserLimit' => self::faker()->randomNumber(),
            'startDate' => \DateTimeImmutable::createFromMutable(self::faker()->dateTime()),
            'usageLimit' => self::faker()->randomNumber(),
        ];
    }

    /**
     * @see https://symfony.com/bundles/ZenstruckFoundryBundle/current/index.html#initialization
     */
    protected function initialize(): static
    {
        return $this
            // ->afterInstantiate(function(Coupon $coupon): void {})
        ;
    }
}
