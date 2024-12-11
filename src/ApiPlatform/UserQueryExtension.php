<?php

declare(strict_types=1);

namespace App\ApiPlatform;

use ApiPlatform\Doctrine\Orm\Extension\QueryCollectionExtensionInterface;
use ApiPlatform\Doctrine\Orm\Extension\QueryItemExtensionInterface;
use ApiPlatform\Doctrine\Orm\Util\QueryNameGeneratorInterface;
use ApiPlatform\Metadata\Operation;
use App\Entity\User;
use Doctrine\ORM\QueryBuilder;
use Symfony\Bundle\SecurityBundle\Security;

class UserQueryExtension implements QueryCollectionExtensionInterface, QueryItemExtensionInterface
{

    public function __construct (
        private readonly Security $security,
    )
    {
    }

    public function applyToCollection(QueryBuilder $queryBuilder, QueryNameGeneratorInterface $queryNameGenerator, string $resourceClass, ?Operation $operation = null, array $context = []): void
    {
        $this->addUserIdEqualsToLoggedInUserId($queryBuilder, $resourceClass);
    }

    public function applyToItem(QueryBuilder $queryBuilder, QueryNameGeneratorInterface $queryNameGenerator, string $resourceClass, array $identifiers, ?Operation $operation = null, array $context = []): void
    {
        $this->addUserIdEqualsToLoggedInUserId($queryBuilder, $resourceClass);
    }

    private function addUserIdEqualsToLoggedInUserId(QueryBuilder $queryBuilder, string $resourceClass): void
    {
        if ( $resourceClass !== User::class ) {
            return;
        }

        /** @var User $loggedInUser */
        $loggedInUser = $this->security->getUser();
        $rootAlias    = $queryBuilder->getRootAliases()[0];

        if ( $loggedInUser ) {
            $queryBuilder->andWhere(sprintf('%s.id = :currentUserId', $rootAlias))
                    ->setParameter('currentUserId', $loggedInUser->getId())
            ;
        }
    }

}