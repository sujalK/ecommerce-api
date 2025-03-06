<?php

declare(strict_types = 1);

namespace App\Tests\Functional\Cart;

use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Zenstruck\Browser\Test\HasBrowser;
use Zenstruck\Foundry\Test\Factories;
use Zenstruck\Foundry\Test\ResetDatabase;

class CartResourceTest extends KernelTestCase
{

    use HasBrowser;
    use ResetDatabase;
    use Factories;

    public function testPostToCreateCartForUser(): void
    {

        $this->browser()
             ->post('/api/carts', [
                 'json' => [

                 ],
                 'headers' => [
                     'Content-Type' => 'application/json',
                 ]
             ])
        ;

    }

}