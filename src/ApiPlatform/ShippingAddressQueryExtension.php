<?php

declare(strict_types = 1);

namespace App\ApiPlatform;

use ApiPlatform\Doctrine\Orm\Extension\QueryCollectionExtensionInterface;
use ApiPlatform\Doctrine\Orm\Extension\QueryItemExtensionInterface;
use ApiPlatform\Doctrine\Orm\Util\QueryNameGeneratorInterface;
use ApiPlatform\Metadata\Operation;
use App\Entity\ShippingAddress;
use App\Entity\User;
use Doctrine\ORM\QueryBuilder;
use Symfony\Bundle\SecurityBundle\Security;

class ShippingAddressQueryExtension implements QueryCollectionExtensionInterface, QueryItemExtensionInterface
{

    public function __construct (
        private readonly Security $security,
    )
    {
    }

    public function applyToCollection(QueryBuilder $queryBuilder, QueryNameGeneratorInterface $queryNameGenerator, string $resourceClass, ?Operation $operation = null, array $context = []): void
    {
        $this->applyOwnerAccessFiltration($resourceClass, $queryBuilder);
    }

    public function applyToItem(QueryBuilder $queryBuilder, QueryNameGeneratorInterface $queryNameGenerator, string $resourceClass, array $identifiers, ?Operation $operation = null, array $context = []): void
    {
        // For GET operation, skip filtering, because if non-owner tries to fetch it, it returns a "403 Forbidden", but if
        // owner tries to fetch it, it defaults to provider (without need of filtering here)
        if (str_ends_with($operation->getName(), '_get')) {
            return;
        }

        if (str_ends_with($operation->getName(), '_patch')) {
            return;
        }

        if (str_ends_with($operation->getName(), '_delete')) {
            return;
        }

        $this->applyOwnerAccessFiltration($resourceClass, $queryBuilder);
    }

    public function applyOwnerAccessFiltration(string $resourceClass, QueryBuilder $queryBuilder): void
    {
        if ($resourceClass !== ShippingAddress::class) {
            return;
        }

        if ($this->security->isGranted('ROLE_ADMIN')) {
            return;
        }

        $user = $this->security->getUser();
        assert($user instanceof User);

        $rootAlias = $queryBuilder->getRootAliases()[0];

        $queryBuilder->andWhere(\sprintf('%s.owner = :owner', $rootAlias))
                     ->setParameter('owner', $user->getId());
    }

}