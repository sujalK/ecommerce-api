<?php

declare(strict_types = 1);

namespace App\DataObjects;

class StripePaymentData
{
    public function __construct (
        public readonly string $transactionId,
        public readonly int $amount,
        public readonly string $currency,
        public readonly string|null $paymentMethodType,
        public readonly string $status,
    ) {
    }
}