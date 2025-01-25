<?php

declare(strict_types = 1);

namespace App\Service;

use App\Contracts\InventoryServiceInterface;
use App\Entity\Inventory;
use App\Exception\InvalidQuantityException;
use App\Exception\InventoryException\InsufficientStockException;
use App\Exception\InventoryException\ProductNotFoundException;
use App\Repository\InventoryRepository;
use Doctrine\ORM\EntityManagerInterface;

class InventoryService implements InventoryServiceInterface
{

    public function __construct (
        private readonly EntityManagerInterface $entityManager,
        private readonly InventoryRepository $repository,
    )
    {
    }

    /**
     * @throws ProductNotFoundException
     * @throws InsufficientStockException
     */
    public function checkInventory(int $productId, int $quantity): void
    {
        // Ensure the requested quantity is valid
        if ($quantity <= 0) {
            throw new InvalidQuantityException("Requested quantity must be greater than zero.");
        }

        // Query the inventory table to get the current stock
        $inventory = $this->entityManager->getRepository(Inventory::class)->findByProductId($productId);

        if (!$inventory) {
            throw new ProductNotFoundException('Product not found in inventory.');
        }

        if ($inventory->getQuantityInStock() < $quantity) {
            throw new InsufficientStockException('Not enough stock available.');
        }
    }

    public function deductQuantityFromInventory(int $productId, int $newQuantity): void
    {
        $inventory = $this->repository->findByProductId($productId);

        $inventory->setQuantityInStock($inventory->getQuantityInStock() - $newQuantity);

        $this->entityManager->persist($inventory);
        $this->entityManager->flush();
    }
}