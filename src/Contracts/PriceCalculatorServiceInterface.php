<?php

declare(strict_types = 1);

namespace App\Contracts;

interface PriceCalculatorServiceInterface
{
    public function calculateTotalPrice(int $quantity, string $unitPrice): string;
}