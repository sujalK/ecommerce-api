<?php

declare(strict_types = 1);

namespace App\Tests\Functional\Discount;

use App\Factory\ApiTokenFactory;
use App\Factory\CartFactory;
use App\Factory\CouponFactory;
use App\Factory\OrderFactory;
use App\Factory\UserFactory;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Zenstruck\Browser\Test\HasBrowser;
use Zenstruck\Foundry\Test\Factories;
use Zenstruck\Foundry\Test\ResetDatabase;

class DiscountResourceTest extends KernelTestCase
{

    use HasBrowser;
    use ResetDatabase;
    use Factories;

    public function testPostToApplyCoupon(): void
    {

        $user = UserFactory::createOne();
        $token = ApiTokenFactory::createOne([
            'expiresAt' => null,
            'owner'     => $user,
        ]);

        CouponFactory::createOne([
            'code'      => 'CODER200',
            'startDate' => new \DateTime('now'),
            'endDate'   => new \DateTime('+5 days'),
        ]);

        CartFactory::createOne([
            'owner' => $user,
            'status' => 'active',
        ]);

        $this->browser()
             ->post('/api/apply-discount', [
                 'json' => [
                     'couponCode' => 'CODER200',
                 ],
                 'headers' => [
                     'Content-Type'  => 'application/ld+json',
                     'Authorization' => 'Bearer '. $token->getToken()
                 ]
             ])
             ->dump()
        ;

    }

}