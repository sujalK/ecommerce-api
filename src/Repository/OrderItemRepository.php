<?php

namespace App\Repository;

use App\Entity\Order;
use App\Entity\OrderItem;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<OrderItem>
 */
class OrderItemRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, OrderItem::class);
    }

    /**
     * Find all order items for a specific Order entity.
     *
     * @param Order $order
     * @return OrderItem[]
     */
    public function findByOrder(Order $order): array
    {
        return $this->createQueryBuilder('oi')
                    ->andWhere('oi.order = :order')
                    ->setParameter('order', $order) // Pass the Order entity directly
                    ->orderBy('oi.id', 'ASC')
                    ->getQuery()
                    ->getResult()
            ;
    }

    /**
     * Get all order items for a given order ID.
     */
    public function findByOrderId(int $orderId): array
    {
        return $this->createQueryBuilder('oi')
                    ->where('oi.order = :orderId')
                    ->setParameter('orderId', $orderId)
                    ->getQuery()
                    ->getResult();
    }

    public function findOneByProductAndUserWithShippedStatus(int $productId, User $user): ?OrderItem
    {
        return $this->createQueryBuilder('oi')
                    ->innerJoin('oi.order', 'o')
                    ->andWhere('oi.product = :product')
                    ->andWhere('o.ownedBy = :user')
                    ->andWhere('o.shippingStatus = :shippingStatus')
                    ->setParameter('product', $productId)
                    ->setParameter('user', $user)
                    ->setParameter('shippingStatus', 'shipped')
                    ->orderBy('oi.id', 'DESC')
                    ->setMaxResults(1)
                    ->getQuery()
                    ->getOneOrNullResult();
    }

    //    /**
    //     * @return OrderItem[] Returns an array of OrderItem objects
    //     */
    //    public function findByExampleField($value): array
    //    {
    //        return $this->createQueryBuilder('o')
    //            ->andWhere('o.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->orderBy('o.id', 'ASC')
    //            ->setMaxResults(10)
    //            ->getQuery()
    //            ->getResult()
    //        ;
    //    }

    //    public function findOneBySomeField($value): ?OrderItem
    //    {
    //        return $this->createQueryBuilder('o')
    //            ->andWhere('o.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }
}
