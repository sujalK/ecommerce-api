<?php

declare(strict_types = 1);

namespace App\ApiPlatform;

use ApiPlatform\Doctrine\Orm\Extension\QueryCollectionExtensionInterface;
use ApiPlatform\Doctrine\Orm\Extension\QueryItemExtensionInterface;
use ApiPlatform\Doctrine\Orm\Util\QueryNameGeneratorInterface;
use ApiPlatform\Metadata\Operation;
use App\Entity\CartItem;
use Doctrine\ORM\QueryBuilder;
use Symfony\Bundle\SecurityBundle\Security;

class CartItemQueryExtension implements QueryItemExtensionInterface, QueryCollectionExtensionInterface
{

    public function __construct (
        private readonly Security $security,
    )
    {
    }

    public function applyToCollection(QueryBuilder $queryBuilder, QueryNameGeneratorInterface $queryNameGenerator, string $resourceClass, ?Operation $operation = null, array $context = []): void
    {
        $this->restrictAccessNonOwner($resourceClass, $queryBuilder);
    }

    public function applyToItem(QueryBuilder $queryBuilder, QueryNameGeneratorInterface $queryNameGenerator, string $resourceClass, array $identifiers, ?Operation $operation = null, array $context = []): void
    {
        $this->restrictAccessNonOwner($resourceClass, $queryBuilder);
    }

    public function restrictAccessNonOwner(string $resourceClass, QueryBuilder $queryBuilder): void
    {
        if (CartItem::class !== $resourceClass) {
            return;
        }

        if ($this->security->isGranted('ROLE_ADMIN')) {
            return;
        }

        $user = $this->security->getUser();

        if ($user) {
            // Get the alias for the CartItem entity
            $rootAlias = $queryBuilder->getRootAliases()[0];

            $queryBuilder
                ->join('App\Entity\Cart', 'cart', 'WITH', "$rootAlias.cart = cart")
                ->andWhere('cart.owner = :user')
                ->setParameter('user', $user);
        }
    }
}