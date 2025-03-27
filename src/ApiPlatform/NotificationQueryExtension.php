<?php

declare(strict_types = 1);

namespace App\ApiPlatform;

use ApiPlatform\Doctrine\Orm\Extension\QueryCollectionExtensionInterface;
use ApiPlatform\Doctrine\Orm\Extension\QueryItemExtensionInterface;
use ApiPlatform\Doctrine\Orm\Util\QueryNameGeneratorInterface;
use ApiPlatform\Metadata\Operation;
use App\Entity\Notification;
use App\Entity\User;
use Doctrine\ORM\QueryBuilder;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\RequestStack;

class NotificationQueryExtension implements QueryCollectionExtensionInterface, QueryItemExtensionInterface
{

    public function __construct (
        private readonly RequestStack $requestStack,
        private readonly Security $security,
    )
    {
    }

    public function applyToCollection(QueryBuilder $queryBuilder, QueryNameGeneratorInterface $queryNameGenerator, string $resourceClass, ?Operation $operation = null, array $context = []): void
    {
        $this->AddAndWhere($resourceClass, $queryBuilder);
    }

    public function applyToItem(QueryBuilder $queryBuilder, QueryNameGeneratorInterface $queryNameGenerator, string $resourceClass, array $identifiers, ?Operation $operation = null, array $context = []): void
    {
        $this->AddAndWhere($resourceClass, $queryBuilder);
    }

    public function AddAndWhere(string $resourceClass, QueryBuilder $queryBuilder): void
    {
        if ($resourceClass !== Notification::class) {
            return;
        }

        $currentRequest = $this->requestStack->getCurrentRequest();

        // skip filtering for the PATCH operation, so that 403 forbidden response is returned.
        if( $currentRequest && $currentRequest->getRealMethod() === 'PATCH') {
            return;
        }

        // don't add filtering to Admin user
        if ($this->security->isGranted('ROLE_ADMIN')) {
            return;
        }

        $user = $this->security->getUser();

        // If user is logged-in then filter data by the logged-in user
        if ($user) {
            assert($user instanceof User);

            $rootAlias = $queryBuilder->getRootAliases()[0];

            $queryBuilder->andWhere(\sprintf('%s.ownedBy = :ownedBy', $rootAlias))
                ->setParameter('ownedBy', $user);
        }

    }
}