<?php

declare(strict_types = 1);

namespace App\ApiPlatform;

use ApiPlatform\Doctrine\Orm\Extension\QueryCollectionExtensionInterface;
use ApiPlatform\Doctrine\Orm\Extension\QueryItemExtensionInterface;
use ApiPlatform\Doctrine\Orm\Util\QueryNameGeneratorInterface;
use ApiPlatform\Metadata\Operation;
use App\Entity\Cart;
use Doctrine\ORM\QueryBuilder;
use Symfony\Bundle\SecurityBundle\Security;

class CartQueryExtension implements QueryItemExtensionInterface, QueryCollectionExtensionInterface
{

    public function __construct (
        private readonly Security $security,
    )
    {
    }

    public function applyToItem(QueryBuilder $queryBuilder, QueryNameGeneratorInterface $queryNameGenerator, string $resourceClass, array $identifiers, ?Operation $operation = null, array $context = []): void
    {
        $this->addOwnerWhere($resourceClass, $queryBuilder);
    }

    public function applyToCollection(QueryBuilder $queryBuilder, QueryNameGeneratorInterface $queryNameGenerator, string $resourceClass, ?Operation $operation = null, array $context = []): void
    {
        $this->addOwnerWhere($resourceClass, $queryBuilder);
    }

    public function addOwnerWhere(string $resourceClass, QueryBuilder $queryBuilder): void
    {
        if ($resourceClass !== Cart::class) {
            return;
        }

        $rootAlias = $queryBuilder->getRootAliases()[0];

        $user = $this->security->getUser();
        if ($this->security->isGranted('ROLE_ADMIN')) {
            return;
        }

        $queryBuilder->andWhere(\sprintf('%s.owner = :owner', $rootAlias))
                     ->setParameter('owner', $user);
    }
}