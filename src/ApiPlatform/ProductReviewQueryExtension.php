<?php

declare(strict_types = 1);

namespace App\ApiPlatform;

use ApiPlatform\Doctrine\Orm\Extension\QueryCollectionExtensionInterface;
use ApiPlatform\Doctrine\Orm\Extension\QueryItemExtensionInterface;
use ApiPlatform\Doctrine\Orm\Util\QueryNameGeneratorInterface;
use ApiPlatform\Metadata\Operation;
use App\Entity\ProductReview;
use App\Entity\User;
use Doctrine\ORM\QueryBuilder;
use Symfony\Bundle\SecurityBundle\Security;

class ProductReviewQueryExtension implements QueryCollectionExtensionInterface, QueryItemExtensionInterface
{

    public function __construct (
        private Security $security,
    )
    {
    }

    public function applyToCollection(QueryBuilder $queryBuilder, QueryNameGeneratorInterface $queryNameGenerator, string $resourceClass, ?Operation $operation = null, array $context = []): void
    {
        $this->addAndWhere($resourceClass, $queryBuilder);
    }

    public function applyToItem(QueryBuilder $queryBuilder, QueryNameGeneratorInterface $queryNameGenerator, string $resourceClass, array $identifiers, ?Operation $operation = null, array $context = []): void
    {
        // If it's a GET operation, then let security handle it, by throwing 403 Forbidden instead of returning a 404
        if (str_ends_with($operation->getName(), '_get')) {
            return;
        }

        $this->addAndWhere($resourceClass, $queryBuilder);
    }

    public function addAndWhere(string $resourceClass, QueryBuilder $queryBuilder): void
    {
        if ($resourceClass !== ProductReview::class) {
            return;
        }

        if ($this->security->isGranted('ROLE_ADMIN')) {
            return;
        }

        /** @var User $user */
        $user = $this->security->getUser();

        $rootAlias = $queryBuilder->getRootAliases()[0];

        if ($user) {
            // for logged-in user
            $queryBuilder->andWhere(\sprintf('%s.owner = :owner OR %s.isActive = :isActive', $rootAlias, $rootAlias))
                ->setParameter('owner', $user)
                ->setParameter('isActive', true)
            ;
        } else {
            // for not logged-in user
            $queryBuilder->andWhere(\sprintf('%s.isActive = :isActive', $rootAlias))
                ->setParameter('isActive', true)
            ;
        }

    }
}