<?php

namespace App\Factory;

use App\Entity\ProductReview;
use Zenstruck\Foundry\Persistence\PersistentProxyObjectFactory;

/**
 * @extends PersistentProxyObjectFactory<ProductReview>
 */
final class ProductReviewFactory extends PersistentProxyObjectFactory
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
        return ProductReview::class;
    }

    /**
     * @see https://symfony.com/bundles/ZenstruckFoundryBundle/current/index.html#model-factories
     *
     * @todo add your default values here
     */
    protected function defaults(): array|callable
    {
        return [
            'createdAt'  => \DateTimeImmutable::createFromMutable(self::faker()->dateTime()),
            'rating'     => self::faker()->randomElement([1, 2, 3, 4, 5]),
            'reviewText' => self::faker()->text(maxNbChars: 50),
        ];
    }

    /**
     * @see https://symfony.com/bundles/ZenstruckFoundryBundle/current/index.html#initialization
     */
    protected function initialize(): static
    {
        return $this
            // ->afterInstantiate(function(ProductReview $productReview): void {})
        ;
    }
}
