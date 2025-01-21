<?php

declare(strict_types = 1);

namespace App\Tests\Functional\CartItem;

use App\Factory\ApiTokenFactory;
use App\Factory\CartFactory;
use App\Factory\CartItemFactory;
use App\Factory\InventoryFactory;
use App\Factory\ProductFactory;
use App\Factory\UserFactory;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Zenstruck\Browser\Test\HasBrowser;
use Zenstruck\Foundry\Test\Factories;
use Zenstruck\Foundry\Test\ResetDatabase;

class CartItemResourceTest extends KernelTestCase
{

    use ResetDatabase;
    use Factories;
    use HasBrowser;

    /**
     * Adds item to the cart, by sending a POST request to the /api/cart_items endpoint
     */
    public function testPostToCreateCartItem(): void
    {
        $product = ProductFactory::createOne([
            'price' => 20
        ]);

        InventoryFactory::createOne([
            'product'             => $product,
            'quantityInStock'     => 200,
            'quantitySold'        => 10,
            'quantityBackOrdered' => 10,
        ]);

        // Create API Token
        $user  = UserFactory::createOne();
        $token = ApiTokenFactory::createOne([
            'owner' => $user,
        ]);
        $this->browser()
            /** sending request to /api/carts which is a modified uriTemplate for the Post() operation inside the CartItemApi */
            ->post('/api/carts', [
                'json' => [
                    'product'  => '/api/products/'. $product->getId(),
                    'quantity' => 10,
                ],
                'headers' => [
                    'Content-Type'  => 'application/json',
                    'Accept'        => 'application/json',
                    'Authorization' => 'Bearer '. $token->getToken(),
                ]
            ])
            ->dump()
            ->assertJsonMatches('totalPrice', "200.00")
        ;
    }

    public function testDeleteToRemoveCartItem(): void
    {

        $user = UserFactory::createOne([
            'email' => 'test_user@gmail.com'
        ]);

        $cart = CartFactory::createOne([
            'owner'      => $user,
            'status'     => 'active',
            'totalPrice' => '400.00',
        ]);

        $product = ProductFactory::createOne([
            'price' => 20.00
        ]);

        $cartItem = CartItemFactory::createOne([
            'cart'                     => $cart,
            'product'                  => $product,
            'quantity'                 => 20,
            'pricePerUnit'             => $product->getPrice(),
            'totalPrice'               => $product->getPrice(),
            'createdAt'                => new \DateTimeImmutable(),
            'updatedAt'                => new \DateTimeImmutable(),
            'discountAmount'           => '10',
            'totalPriceAfterDiscount'  => $product->getPrice(),
        ]);

        $token = ApiTokenFactory::createOne([
            'owner' => $user,
        ]);

        $this->browser()
             ->delete('/api/cart_items/'. $cartItem->getId(), [
                 'headers' => [
                     'Authorization' => 'Bearer '. $token->getToken(),
                 ]
             ])
             ->dump()
        ;

    }

}