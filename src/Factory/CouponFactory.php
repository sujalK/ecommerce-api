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
            'code' => self::faker()->text(20),
            'description' => self::faker()->text(),
            'discountType' => 'fixed',
            'discountValue' => '5.00',
            'endDate' => new \DateTimeImmutable('+5 days'),
            'isActive' => true,
            'minimumCartValue' => '10.00',
            'singleUserLimit' => 10,
            'startDate' => new \DateTimeImmutable('now'),
            'usageLimit' => '100',
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
