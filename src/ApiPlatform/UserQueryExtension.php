<?php

declare(strict_types = 1);

namespace App\ApiPlatform;

use ApiPlatform\Doctrine\Orm\Extension\QueryCollectionExtensionInterface;
use ApiPlatform\Doctrine\Orm\Extension\QueryItemExtensionInterface;
use ApiPlatform\Doctrine\Orm\Util\QueryNameGeneratorInterface;
use ApiPlatform\Metadata\Operation;
use App\Entity\User;
use Doctrine\ORM\QueryBuilder;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\Request;

class UserQueryExtension implements QueryCollectionExtensionInterface, QueryItemExtensionInterface
{
    /*
     * Request methods to skip filtering to let security: in #[AApiResource] handle
     * the situation.
     */
    private const array _SKIP_REQUEST_METHODS = ['GET', 'PATCH', 'DELETE'];

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
        $request = $context['request'] ?? null;

        if ( $request ) {
            assert($request instanceof Request);

            $requestMethod = $request->getRealMethod();

            // If our request method falls under allowed skip values, then do not apply filter
            if (in_array($requestMethod, self::_SKIP_REQUEST_METHODS)) {
                return;
            }
        }

        $requestUri   = $context['request_uri'];
        $supportedUri = '/api/users';

        // if api request is not from the /api/users, skip the filtering
        if ( ! str_contains($requestUri, $supportedUri) ) {
            return;
        }

        $this->addUserIdEqualsToLoggedInUserId($queryBuilder, $resourceClass);
    }

    private function addUserIdEqualsToLoggedInUserId(QueryBuilder $queryBuilder, string $resourceClass): void
    {
        if ( $resourceClass !== User::class ) {
            return;
        }

        if ($this->security->isGranted('ROLE_ADMIN')) {
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