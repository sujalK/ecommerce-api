<?php

declare(strict_types = 1);

namespace App\ApiResource\Coupon;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Post;
use App\DataObjects\ApplyPendingOrderDiscountData;
use App\State\ApplyCouponToPendingOrderStateProcessor;
use App\State\RemoveCouponFromPendingOrder;

#[ApiResource(
    shortName: 'OrderCoupon',
    operations: [
        new Post (
            uriTemplate: '/orders/{orderId}/apply-coupon',
            status: 200,
            input: ApplyPendingOrderDiscountData::class,
            provider: null,
            processor: ApplyCouponToPendingOrderStateProcessor::class
        ),
        new Post (
            uriTemplate: '/orders/{orderId}/remove-coupon',
            status: 200,
            input: false,
            provider: null,
            processor: RemoveCouponFromPendingOrder::class
        )
    ],
    security: 'is_granted("ROLE_USER")',
)]
class PendingOrderCoupon {}