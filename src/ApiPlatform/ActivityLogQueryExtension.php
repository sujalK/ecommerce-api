<?php

declare(strict_types = 1);

namespace App\ApiPlatform;

use ApiPlatform\Doctrine\Orm\Extension\QueryCollectionExtensionInterface;
use ApiPlatform\Doctrine\Orm\Util\QueryNameGeneratorInterface;
use ApiPlatform\Metadata\Operation;
use App\Entity\ActivityLog;
use Doctrine\ORM\QueryBuilder;
use Symfony\Bundle\SecurityBundle\Security;

class ActivityLogQueryExtension implements QueryCollectionExtensionInterface
{
    public function __construct(
        private readonly Security $security,
    ) {}

    public function applyToCollection(QueryBuilder $queryBuilder, QueryNameGeneratorInterface $queryNameGenerator, string $resourceClass, ?Operation $operation = null, array $context = []): void
    {
        $this->addWhere($queryBuilder, $resourceClass);
        // $this->sortAdminOrder($queryBuilder, $resourceClass);
    }

    private function addWhere(QueryBuilder $queryBuilder, string $resourceClass)
    {
        if ($resourceClass !== ActivityLog::class) {
            return;
        }

        // Show everything for admin
        if ($this->security->isGranted('ROLE_ADMIN')) {
            return; // No filter for admin: show everything
        }

        // For non-admin users, filter by their activity logs
        $rootAlias = $queryBuilder->getRootAliases()[0];
        $user = $this->security->getUser();
        if ($user) {
            $queryBuilder->andWhere(sprintf('%s.owner = :owner', $rootAlias))
                ->setParameter('owner', $user);
        }
    }

    private function sortAdminOrder(QueryBuilder $queryBuilder, string $resourceClass): void
    {
        if ($resourceClass !== ActivityLog::class) {
            return;
        }

        $rootAlias = $queryBuilder->getRootAliases()[0];
        $user      = $this->security->getUser();

        // If user is admin, apply specific sorting to show their logs first
        if ($this->security->isGranted('ROLE_ADMIN')) {
            // Show admin logs first, then others (using CASE WHEN)
            $queryBuilder->addOrderBy (
                sprintf('CASE WHEN %s.owner = :currentUser THEN 0 ELSE 1 END', $rootAlias),
                'ASC'
            );

            $queryBuilder->setParameter('currentUser', $user);
        }

        $this->applyCommonSorting($queryBuilder, $rootAlias);
    }

    private function applyCommonSorting(QueryBuilder $queryBuilder, string $rootAlias): void
    {
        $queryBuilder->addOrderBy(sprintf('%s.createdAt', $rootAlias), 'DESC');
    }

}