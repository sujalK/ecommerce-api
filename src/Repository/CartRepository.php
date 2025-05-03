<?php

declare(strict_types = 1);

namespace App\Repository;

use App\Entity\Cart;
use App\Entity\CartItem;
use App\Exception\CartItemNotFoundException;
use App\Exception\CartNotFoundException;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Cart>
 */
class CartRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Cart::class);
    }

    public function getCart(int $cartId): Cart
    {

        $cart = $this->find($cartId);

        // $cart = $this->entityManager->find(Cart::class, $cartId);

        if (!$cart) {
            throw new CartNotFoundException("Cart with not found");
        }

        return $cart;
    }

    /**
     * @return iterable<Cart>
     */
    public function findCartsToRemind(): iterable
    {
        return $this->createQueryBuilder('c')
                    ->andWhere('c.status = :status')
                    ->andWhere('c.reminderSentAt IS NULL')
                    ->andWhere('c.createdAt < :cutoff')
                    ->setParameter('status', 'active')
                    ->setParameter('cutoff', new \DateTimeImmutable('-24 hours'))
                    ->getQuery()
                    ->toIterable();
    }

    //    /**
    //     * @return Cart[] Returns an array of Cart objects
    //     */
    //    public function findByExampleField($value): array
    //    {
    //        return $this->createQueryBuilder('c')
    //            ->andWhere('c.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->orderBy('c.id', 'ASC')
    //            ->setMaxResults(10)
    //            ->getQuery()
    //            ->getResult()
    //        ;
    //    }

    //    public function findOneBySomeField($value): ?Cart
    //    {
    //        return $this->createQueryBuilder('c')
    //            ->andWhere('c.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }
}
