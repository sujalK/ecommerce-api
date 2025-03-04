<?php

declare(strict_types = 1);

namespace App\ApiResource\Coupon;

use ApiPlatform\Metadata\ApiProperty;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Link;
use ApiPlatform\Metadata\Post;
use App\Entity\Order;
use App\State\ApplyCouponToPendingOrderStateProcessor;
use Symfony\Component\Validator\Constraints as Assert;

#[ApiResource(
    shortName: 'OrderCoupon',
    operations: [
        new Post (
            uriTemplate: '/orders/{orderId}/apply-coupon',
            uriVariables: [
                'orderId' => new Link (
                    fromProperty: 'id',
                    fromClass: Order::class,
                )
            ],
            processor: ApplyCouponToPendingOrderStateProcessor::class
        )
    ],
    security: 'is_granted("ROLE_USER")',
)]
class PendingOrderCoupon
{

    #[ApiProperty(identifier: true, genId: false)]
    public ?int $id = null;

    #[Assert\Regex (
        pattern: '/^[a-zA-Z0-9]+$/',
        message: 'Invalid coupon code.',
    )]
    #[ApiProperty(readable: true, writable: true)]
    public ?string $couponCode = null;

}