<?php

declare(strict_types = 1);

namespace App\Tests\Functional\Payment;

use App\Factory\OrderFactory;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Zenstruck\Browser\Test\HasBrowser;
use Zenstruck\Foundry\Test\Factories;
use Zenstruck\Foundry\Test\ResetDatabase;

class PaymentResourceTest extends KernelTestCase
{

    use Factories;
    use HasBrowser;
    use ResetDatabase;

    public function testPostToCreatePaymentInfo(): void
    {

        $order = OrderFactory::createOne([
            'total_price' => '10.11',
            'createdAt'   => new \DateTimeImmutable('now', new \DateTimeZone('UTC'))
        ]);

        $this->browser()
             ->post('/api/payments', [
                 'json' => [
                     'order'          => '/api/orders/'. $order->getId(),
                     'paymentMethod'  => 'card',
                     'paymentStatus'  => 'paid',
                     'amount'         => '10.10',
                     'paymentDate'    => (new \DateTime('now'))->format('Y-m-d\TH:i:sP'),
                     'transactionId'  => '1001',
                     'billingAddress' => '123 Main St.',
                     'lineItems'      => [
                         [
                             'name'     => 'T-Shirt',
                             'quantity' => 1,
                             'price'    => 10,
                         ]
                     ]
                 ],
                 'headers' => [
                     'Content-Type' => 'application/json',
                     'Accept'       => 'application/ld+json',
                 ]
             ])
             // ->dump()
        ;

    }

}