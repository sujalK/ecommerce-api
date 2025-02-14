<?php

namespace App\Repository;

use App\Entity\Inventory;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Inventory>
 */
class InventoryRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Inventory::class);
    }

    public function findByProductId(int $productId): ?Inventory
    {
        return $this->createQueryBuilder('i')
                    ->andWhere('i.product = :productId')
                    ->setParameter('productId', $productId)
                    ->getQuery()
                    ->getOneOrNullResult();
    }

    public function deductInventory(int $productId, int $quantity): void
    {
        $entityManager = $this->getEntityManager();
        $inventory = $this->findOneBy(['product' => $productId]);

        if (!$inventory) {
            throw new \RuntimeException("Inventory record not found for product ID: $productId");
        }

        if ($inventory->getQuantityInStock() < $quantity) {
            throw new \RuntimeException("Not enough stock for product ID: $productId");
        }

        $inventory->setQuantityInStock($inventory->getQuantityInStock() - $quantity);
        $inventory->setQuantitySold($inventory->getQuantitySold() + $quantity);

        $entityManager->persist($inventory);
        $entityManager->flush();
    }

    //    /**
    //     * @return Inventory[] Returns an array of Inventory objects
    //     */
    //    public function findByExampleField($value): array
    //    {
    //        return $this->createQueryBuilder('i')
    //            ->andWhere('i.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->orderBy('i.id', 'ASC')
    //            ->setMaxResults(10)
    //            ->getQuery()
    //            ->getResult()
    //        ;
    //    }

    //    public function findOneBySomeField($value): ?Inventory
    //    {
    //        return $this->createQueryBuilder('i')
    //            ->andWhere('i.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }
}
