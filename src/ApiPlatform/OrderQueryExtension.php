<?php

declare(strict_types = 1);

namespace App\ApiPlatform;

use ApiPlatform\Doctrine\Orm\Extension\QueryCollectionExtensionInterface;
use ApiPlatform\Doctrine\Orm\Extension\QueryItemExtensionInterface;
use ApiPlatform\Doctrine\Orm\Util\QueryNameGeneratorInterface;
use ApiPlatform\Metadata\Operation;
use App\Entity\Order;
use App\Entity\User;
use Doctrine\ORM\QueryBuilder;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\RequestStack;

class OrderQueryExtension implements QueryCollectionExtensionInterface, QueryItemExtensionInterface
{

    public function __construct (
        private readonly RequestStack $requestStack,
        private readonly Security $security,
    )
    {
    }

    public function applyToCollection(QueryBuilder $queryBuilder, QueryNameGeneratorInterface $queryNameGenerator, string $resourceClass, ?Operation $operation = null, array $context = []): void
    {
        $this->addAndWhereUserIdEquals($resourceClass, $queryBuilder);
    }

    public function applyToItem(QueryBuilder $queryBuilder, QueryNameGeneratorInterface $queryNameGenerator, string $resourceClass, array $identifiers, ?Operation $operation = null, array $context = []): void
    {
        $this->addAndWhereUserIdEquals($resourceClass, $queryBuilder);
    }

    public function addAndWhereUserIdEquals(string $resourceClass, QueryBuilder $queryBuilder): void
    {
        if ($resourceClass !== Order::class) {
            return;
        }

        $request = $this->requestStack->getCurrentRequest();
        if ($request && $request->getRealMethod() === 'GET') {
            return;
        }

        // Allow admin to view everything, i.e. do not restrict admin
        // i.e. skip admin from this filter
        if ($this->security->isGranted('ROLE_ADMIN')) {
            return;
        }

        /** @var User $user */
        $user      = $this->security->getUser();

        // get the root alias
        $rootAlias = $queryBuilder->getRootAliases()[0];

        if ( $user ) {
            $queryBuilder->andWhere(\sprintf('%s.ownedBy = :owner', $rootAlias))
                         ->setParameter('owner', $user);
        }
    }

}