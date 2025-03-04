<?php

declare(strict_types = 1);

namespace App\Tests\Functional\PendingOrderCoupon;

use App\Factory\ApiTokenFactory;
use App\Factory\OrderFactory;
use App\Factory\UserFactory;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Zenstruck\Browser\Test\HasBrowser;
use Zenstruck\Foundry\Test\Factories;
use Zenstruck\Foundry\Test\ResetDatabase;

class PendingOrderCouponResourceTest extends KernelTestCase
{

    use HasBrowser;
    use ResetDatabase;
    use Factories;

    public function testPostToApplyCouponForPendingOrder(): void
    {

        $user = UserFactory::createOne([
            'roles'    => ['ROLE_ADMIN'],
        ]);
        $token = ApiTokenFactory::createOne([
            'expiresAt' => null,
            'owner'     => $user,
        ]);

        $this->browser()
             ->post('/api/orders/1/apply-coupon', [
                 'headers' => [
                     'Accept'       => 'application/json',
                     'Content-Type' => 'application/json',
                     'Authorization' => 'Bearer ' . $token->getToken(),
                 ],
                 'json' => [
                     'couponCode' => 'test',
                 ]
             ])
             ->dump()
        ;

    }

}