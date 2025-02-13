<?php

declare(strict_types = 1);

namespace App\Tests\Functional\Order;

use App\Factory\ApiTokenFactory;
use App\Factory\CartFactory;
use App\Factory\CartItemFactory;
use App\Factory\ProductFactory;
use App\Factory\ShippingAddressFactory;
use App\Factory\ShippingMethodFactory;
use App\Factory\UserFactory;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Zenstruck\Browser\Test\HasBrowser;
use Zenstruck\Foundry\Test\Factories;
use Zenstruck\Foundry\Test\ResetDatabase;

class OrderResourceTest extends KernelTestCase
{

    use HasBrowser;
    use Factories;
    use ResetDatabase;

    public function testPostToPlaceOrder(): void
    {
        $user  = UserFactory::createOne();
        $token = ApiTokenFactory::createOne([
            'owner'     => $user,
            'expiresAt' => null,
        ]);

        // Create Cart
        $cart = CartFactory::createOne([
            'owner'  => $user,
            'status' => 'active',
            'createdAt' => new \DateTime('now', new \DateTimeZone('UTC'))
        ]);
        $product = ProductFactory::createOne();

        // Create CartItem
        CartItemFactory::createMany(2, function() use ($cart, $product) {
            return [
                'cart'                    => $cart,
                'product'                 => $product,
                'quantity'                => 2,
                'price_per_unit'          => 199.99,
                'total_price'             => 399.98,
                'createdAt'               => new \DateTime('now', new \DateTimeZone('UTC')),
                'discountAmount'          => 20.00,
                'totalPriceAfterDiscount' => 379.98,
            ];
        });

        $shippingAddress = ShippingAddressFactory::createOne();
        $shippingMethod  = ShippingMethodFactory::createOne();

        $this->browser()
             ->post('/api/orders', [
                 'json' => [
                     'ownedBy'         => '/api/users/'. $user->getId(),
                     'totalPrice'      => "100.00",
                     'shippingAddress' => '/api/shipping_addresses/'. $shippingAddress->getId(),
                     'shippingMethod'  => '/api/shipping_methods/'. $shippingMethod->getId(),
                 ],
                 'headers' => [
                     'Content-Type'  => 'application/json',
                     'Authorization' => 'Bearer '. $token->getToken(),
                 ]
             ])
             ->dump()
        ;

    }

}