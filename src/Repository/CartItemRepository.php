<?php

declare(strict_types = 1);

namespace App\Repository;

use App\Entity\Cart;
use App\Entity\CartItem;
use App\Exception\CartItemNotFoundException;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * @extends ServiceEntityRepository<CartItem>
 */
class CartItemRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, CartItem::class);
    }

    public function getCartItem(int $id): CartItem
    {

        $cartItem = $this->find($id);
        // $cartItem = $this->entityManager->find(CartItem::class, $id);

        if (!$cartItem) {
            throw new CartItemNotFoundException('Cart item not found');
        }

        return $cartItem;
    }

    public function countRemainingItemsForUserInCart(?UserInterface $user, Cart $cart): int
    {
        return (int) $this->createQueryBuilder('ci')
                            ->innerJoin('ci.cart', 'c')
                            ->where('c.owner = :user') // Ensure it is the logged-in user
                            ->andWhere('c = :cart') // Use the Cart object directly
                            ->setParameter('user', $user)
                            ->setParameter('cart', $cart) // Pass the Cart entity
                            ->select('COUNT(ci.id)') // Only count the items
                            ->getQuery()
                            ->getSingleScalarResult(); // Get a single value (count)
    }

    public function deleteByCart(Cart $cart): void
    {
        $this->createQueryBuilder('ci')
            ->delete()
            ->where('ci.cart = :cart')
            ->setParameter('cart', $cart)
            ->getQuery()
            ->execute();
    }

    //    /**
    //     * @return CartItem[] Returns an array of CartItem objects
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

    //    public function findOneBySomeField($value): ?CartItem
    //    {
    //        return $this->createQueryBuilder('c')
    //            ->andWhere('c.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }
}
