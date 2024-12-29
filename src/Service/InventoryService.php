<?php

declare(strict_types = 1);

namespace App\Service;

use App\Contracts\InventoryServiceInterface;
use App\Entity\Inventory;
use App\Exception\InventoryException\InsufficientStockException;
use App\Exception\InventoryException\ProductNotFoundException;
use Doctrine\ORM\EntityManagerInterface;

class InventoryService implements InventoryServiceInterface
{

    public function __construct (
        private readonly EntityManagerInterface $entityManager,
    )
    {
    }

    /**
     * @throws ProductNotFoundException
     * @throws InsufficientStockException
     */
    public function checkInventory(int $productId, int $quantity): void
    {
        // Query the inventory table to get the current stock
        $inventory = $this->entityManager->getRepository(Inventory::class)->findByProductId($productId);

        if (!$inventory) {
            throw new ProductNotFoundException('Product not found in inventory.');
        }

        if ($inventory->getQuantityInStock() < $quantity) {
            throw new InsufficientStockException('Not enough stock available.');
        }
    }
}