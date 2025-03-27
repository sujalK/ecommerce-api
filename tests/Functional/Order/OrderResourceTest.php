<?php

declare(strict_types = 1);

namespace App\Tests\Functional\Order;

use App\Factory\ApiTokenFactory;
use App\Factory\CartFactory;
use App\Factory\CartItemFactory;
use App\Factory\CouponFactory;
use App\Factory\OrderFactory;
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

    public function testPostToAddAndRemoveCouponFromPendingOrder(): void
    {

        $user = UserFactory::createOne();

        CouponFactory::createOne([
            'code'                               => 'SUMMER10',
            'discount_type'                      => 'percentage',
            'discount_value'                     => 10.00,
            'max_discount_amount_for_percentage' => 50.00,
            'applies_to'                         => ['category' => 78], // Ensure this is stored as JSON if needed
            'minimum_cart_value'                 => 20.00,
            'start_date'                         => new \DateTime('2024-12-25 08:40:12'),
            'end_date'                           => new \DateTime('2025-03-28 23:59:59'),
            'usage_limit'                        => 100,
            'single_user_limit'                  => 5,
            'description'                        => '10% off for minimum order of Rs. 5000',
            'is_active'                          => true,
        ]);

        $order = OrderFactory::createOne([
            'ownedBy' => $user,
        ]);

        $apiToken = ApiTokenFactory::createOne([
            'expiresAt' => null,
            'owner'     => $user,
        ]);

        // Perform testing
        $this->browser()
             ->post("/api/orders/{$order->getId()}/apply-coupon", [
                 'json' => [
                     'couponCode' => 'SUMMER10'
                 ],
                 'headers' => [
                     'Content-Type'  => 'application/json',
                     'Authorization' => 'Bearer '. $apiToken->getToken()
                 ]
             ])
             ->assertStatus(200)
        ;

        // remove Coupon
        $this->browser()
             ->post('/api/orders/1/remove-coupon', [
                 'headers' => [
                     'Authorization' => 'Bearer '. $apiToken->getToken()
                 ]
             ])
             ->assertStatus(200)
        ;

    }

    public function testOwnerCanOnlyFetchOrder(): void
    {

        $user  = UserFactory::createOne();
        $token = ApiTokenFactory::createOne([
            'owner'     => $user,
            'expiresAt' => null,
        ]);

        $order = OrderFactory::createOne([
            'ownedBy' => $user,
        ]);

        $this->browser()
             ->get('/api/orders/'. $order->getId(), [
                 'headers' => [
                     'Authorization' => 'Bearer '. $token->getToken(),
                     'Accept'        => 'application/json',
                 ]
             ])
             ->assertStatus(200);

    }

}