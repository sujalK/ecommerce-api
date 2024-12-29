<?php

declare(strict_types = 1);

namespace App\Tests\Functional\Inventory;

use App\Factory\ApiTokenFactory;
use App\Factory\ProductFactory;
use App\Factory\UserFactory;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Zenstruck\Browser\Test\HasBrowser;
use Zenstruck\Foundry\Test\Factories;
use Zenstruck\Foundry\Test\ResetDatabase;

class InventoryResourceTest extends KernelTestCase
{

    use HasBrowser;
    use Factories;
    use ResetDatabase;

    public function testPostToCreateInventory(): void
    {
        // Create a Product
        $product = ProductFactory::createOne();

        $user  = UserFactory::createOne();
        $token = ApiTokenFactory::createOne([
            'owner'  => $user,
        ]);

        $this->browser()
             ->post('/api/inventories', [
                 'json' => [
                     'quantityInStock'     => 1,
                     'quantitySold'        => 10,
                     'quantityBackOrdered' => 0,
                     'product'             => '/api/products/'. $product->getId(),
                 ],
                 'headers' => [
                     'Content-Type' => 'application/ld+json',
                     'Authorization' => 'Bearer '. $token->getToken()
                 ]
             ])
             ->dump()
        ;

    }

}