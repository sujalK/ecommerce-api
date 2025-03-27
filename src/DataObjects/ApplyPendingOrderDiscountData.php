<?php

declare(strict_types = 1);

namespace App\DataObjects;

use Symfony\Component\Validator\Constraints as Assert;

class ApplyPendingOrderDiscountData
{
    #[Assert\NotBlank]
    #[Assert\Regex(
        pattern: '/^[a-zA-Z0-9]+$/',
        message: 'Invalid coupon code.',
    )]
    public ?string $couponCode = null;
}