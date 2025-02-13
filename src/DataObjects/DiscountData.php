<?php

declare(strict_types = 1);

namespace App\DataObjects;

class DiscountData
{
    public function __construct (
        public ?string $discountType  = null,
        public ?string $discountValue = null,
        public ?string $appliesTo     = null,
        public string $status         = 'success',
        public ?string $message       = 'Coupon applied successfully.',
    )
    {
    }
}