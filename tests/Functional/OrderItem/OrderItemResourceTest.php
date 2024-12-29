<?php

declare(strict_types = 1);

namespace App\Tests\Functional\OrderItem;

use App\Factory\OrderFactory;
use App\Factory\OrderItemFactory;
use App\Factory\ProductFactory;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Zenstruck\Browser\Test\HasBrowser;
use Zenstruck\Foundry\Test\Factories;
use Zenstruck\Foundry\Test\ResetDatabase;

class OrderItemResourceTest extends KernelTestCase
{
    use HasBrowser;
    use ResetDatabase;
    use Factories;

    public function testPostToCreateOrderItem(): void
    {

        $order   = OrderFactory::createOne();
        $product = ProductFactory::createOne();

        $this->browser()
             ->post('/api/order_items', [
                 'json' => [
                     'order'   =>  '/api/orders/'. $order->getId(),
                     'product'        =>  '/api/products/'. $product->getId(),
                     'quantity'       => 20,
                     'unitPrice'      => '5.00',
                     'totalPrice'     => '100.00',
                     'discountAmount' => '100.00',
                     'createdAt'      => (new \DateTimeImmutable())->format(\DateTimeInterface::ATOM), // Use ISO 8601 format
                     'updatedAt'      => (new \DateTimeImmutable())->format(\DateTimeInterface::ATOM),
                 ],
                 'headers' => [
                     'Content-Type' => 'application/ld+json'
                 ]
             ])
             ->dump()
        ;

    }

}