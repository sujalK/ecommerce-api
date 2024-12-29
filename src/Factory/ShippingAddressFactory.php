<?php

namespace App\Factory;

use App\Entity\ShippingAddress;
use Zenstruck\Foundry\Persistence\PersistentProxyObjectFactory;

/**
 * @extends PersistentProxyObjectFactory<ShippingAddress>
 */
final class ShippingAddressFactory extends PersistentProxyObjectFactory
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
        return ShippingAddress::class;
    }

    /**
     * @see https://symfony.com/bundles/ZenstruckFoundryBundle/current/index.html#model-factories
     *
     * @todo add your default values here
     */
    protected function defaults(): array|callable
    {
        return [
            'addressLine1' => self::faker()->text(100),
            'city' => self::faker()->text(100),
            'country' => self::faker()->text(100),
            'createdAt' => \DateTimeImmutable::createFromMutable(self::faker()->dateTime()),
            'owner' => UserFactory::new(),
            'phoneNumber' => self::faker()->text(15),
        ];
    }

    /**
     * @see https://symfony.com/bundles/ZenstruckFoundryBundle/current/index.html#initialization
     */
    protected function initialize(): static
    {
        return $this
            // ->afterInstantiate(function(ShippingAddress $shippingAddress): void {})
        ;
    }
}
