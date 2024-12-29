<?php

declare(strict_types = 1);

namespace App\Contracts;

interface InventoryServiceInterface
{
    public function checkInventory(int $productId, int $quantity): void;
}