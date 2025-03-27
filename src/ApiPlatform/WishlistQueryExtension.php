<?php

declare(strict_types = 1);

namespace App\ApiPlatform;

use ApiPlatform\Doctrine\Orm\Extension\QueryCollectionExtensionInterface;
use ApiPlatform\Doctrine\Orm\Extension\QueryItemExtensionInterface;
use ApiPlatform\Doctrine\Orm\Util\QueryNameGeneratorInterface;
use ApiPlatform\Metadata\Operation;
use App\Entity\User;
use App\Entity\Wishlist;
use Doctrine\ORM\QueryBuilder;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\Request;

class WishlistQueryExtension implements QueryCollectionExtensionInterface, QueryItemExtensionInterface
{
    /*
     * Request methods to skip for while fetching single item
     */
    private const array SKIP_REQUESTS = ['GET', 'DELETE'];

    public function __construct (
        private readonly Security $security,
    )
    {
    }

    public function applyToCollection(QueryBuilder $queryBuilder, QueryNameGeneratorInterface $queryNameGenerator, string $resourceClass, ?Operation $operation = null, array $context = []): void
    {
        $this->addAndWhere($resourceClass, $queryBuilder);
    }

    public function applyToItem(QueryBuilder $queryBuilder, QueryNameGeneratorInterface $queryNameGenerator, string $resourceClass, array $identifiers, ?Operation $operation = null, array $context = []): void
    {

        $request = $context['request'] ?? null;

        if ($request) {
            assert($request instanceof Request);

            $requestMethod = $request->getRealMethod();

            if (in_array($requestMethod, self::SKIP_REQUESTS)) {
                return;
            }
        }

        $this->addAndWhere($resourceClass, $queryBuilder);
    }

    public function addAndWhere(string $resourceClass, QueryBuilder $queryBuilder): void
    {
        if ($resourceClass !== Wishlist::class) {
            return;
        }

        if ($this->security->isGranted('ROLE_ADMIN')) {
            return;
        }

        $user = $this->security->getUser();
        assert($user instanceof User);

        $rootAlias = $queryBuilder->getRootAliases()[0];

        $queryBuilder->andWhere(\sprintf('%s.ownedBy = :ownedBy', $rootAlias))
                     ->setParameter('ownedBy', $user);
    }
}