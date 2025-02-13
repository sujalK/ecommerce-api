<?php

declare(strict_types = 1);

namespace App\Repository;

use App\Entity\Order;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Order>
 */
class OrderRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Order::class);
    }

    public function findMostRecentPendingOrder(User $user): ?Order
    {
        return $this->createQueryBuilder('o')
             ->andWhere('o.ownedBy = :user')
             ->andWhere('o.paymentStatus = :paymentStatus')
             ->orderBy('o.createdAt', 'DESC')
             ->setParameter('user', $user)
             ->setParameter('paymentStatus', 'pending')
             ->setMaxResults(1)
             ->getQuery()
             ->getOneOrNullResult()
            ;
    }

    public function findMostRecentPendingByCouponCode(string $couponCode): ?Order
    {
        return $this->createQueryBuilder('o')
                    ->andWhere('o.couponCode = :couponCode')
                    ->andWhere('o.paymentStatus = :paymentStatus')
                    ->setParameter('couponCode', $couponCode)
                    ->setParameter('paymentStatus', 'pending')
                    ->orderBy('o.createdAt', 'DESC')
                    ->setMaxResults(1)
                    ->getQuery()
                    ->getOneOrNullResult()
            ;
    }

    public function findOrdersByCouponCodeAndUser(string $couponCode, User $user): array
    {
        return $this->createQueryBuilder('o')
                    ->andWhere('o.couponCode = :couponCode')
                    ->andWhere('o.ownedBy = :user') // Filters by user
                    ->setParameter('couponCode', $couponCode)
                    ->setParameter('user', $user)
                    ->orderBy('o.createdAt', 'DESC') // Optional: adjust sorting as needed
                    ->getQuery()
                    ->getResult();
    }

    public function findUsedCouponsCount(string $couponCode, User $user): int
    {
        return (int) $this->createQueryBuilder('o')
            ->select('COUNT(o.id)')
            ->where('o.couponCode = :couponCode')  // Check the coupon code in the order
            ->andWhere('o.ownedBy = :user')         // Ensure the user owns the order
            ->setParameter('couponCode', $couponCode)
            ->setParameter('user', $user)
            ->getQuery()
            ->getSingleScalarResult();
    }

    public function findUsedCouponsCountGlobally(string $couponCode): int
    {
        return (int) $this->createQueryBuilder('o')
                        ->select('COUNT(o.id)')
                        ->where('o.couponCode = :couponCode')
                        ->setParameter('couponCode', $couponCode)
                        ->getQuery()
                        ->getSingleScalarResult();
    }


//    /**
//     * @return Order[] Returns an array of Order objects
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

//    public function findOneBySomeField($value): ?Order
//    {
//        return $this->createQueryBuilder('o')
//            ->andWhere('o.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
