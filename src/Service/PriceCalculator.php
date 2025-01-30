<?php

declare(strict_types = 1);

namespace App\Service;

use App\Contracts\PriceCalculatorServiceInterface;

class PriceCalculator implements PriceCalculatorServiceInterface
{
    public function calculateTotalPrice(int $quantity, string $unitPrice): string
    {
        return bcmul(num1: (string) $quantity, num2: $unitPrice, scale: 2);
    }


}