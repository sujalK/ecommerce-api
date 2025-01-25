<?php

declare(strict_types = 1);

namespace App\Tests\Functional\CartItem;

use App\Entity\ApiToken;
use App\Factory\ApiTokenFactory;
use App\Factory\CartFactory;
use App\Factory\CartItemFactory;
use App\Factory\InventoryFactory;
use App\Factory\ProductFactory;
use App\Factory\UserFactory;
use DateTimeImmutable;
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
     * Adds item to the cart, by sending a POST request to the /api/carts endpoint
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
            'createdAt'                => new DateTimeImmutable(),
            'updatedAt'                => new DateTimeImmutable(),
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
    
    public function testPatchToUpdateCartDoesNotAllowProductReplacement(): void
    {

        $cart    = CartFactory::createOne([
            'totalPrice' => '75.00',
        ]);

        $product = ProductFactory::createOne();
        $product2 = ProductFactory::createOne();

        $cartItem = CartItemFactory::createOne(
            [
                'product'      => $product,
                'quantity'     => 5,
                'pricePerUnit' => 15,
                'totalPrice'   => '75.00',
                'createdAt'    => new DateTimeImmutable('now', new \DateTimezone('UTC')),
            ]
        );

        $user     = UserFactory::createOne();
        $apiToken = ApiTokenFactory::createOne([
            'owner'     => $user,
            'expiresAt' => null,
            'scopes'    => ['ROLE_ADMIN']
        ]);

        $this->browser()
             ->patch('/api/cart_items/'. $cartItem->getId(), [
                 'json' => [
                     'product'      => '/api/products/'. $product2->getId(),
                     'quantity'     => 10,
                     'totalPrice'   => '100.00',
                     'pricePerUnit' =>  10,
                 ],
                 'headers' => [
                     // 'Content-Type' => 'application/merge-patch+json',
                     'Authorization' => 'Bearer '. $apiToken->getToken(),
                 ]
             ])
             ->dump()
        ;

    }

}