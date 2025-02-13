<?php

declare(strict_types = 1);

namespace App\ApiPlatform;

use ApiPlatform\Doctrine\Orm\Extension\QueryCollectionExtensionInterface;
use ApiPlatform\Doctrine\Orm\Extension\QueryItemExtensionInterface;
use ApiPlatform\Doctrine\Orm\Util\QueryNameGeneratorInterface;
use ApiPlatform\Metadata\Operation;
use App\Entity\Payment;
use App\Entity\User;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\QueryBuilder;
use Symfony\Bundle\SecurityBundle\Security;

class PaymentQueryExtension implements QueryCollectionExtensionInterface, QueryItemExtensionInterface
{

    public function __construct (
        private readonly Security $security,
    )
    {
    }

    public function applyToCollection(QueryBuilder $queryBuilder, QueryNameGeneratorInterface $queryNameGenerator, string $resourceClass, ?Operation $operation = null, array $context = []): void
    {
        $this->restrictQuery($queryBuilder, $resourceClass);
    }

    public function applyToItem(QueryBuilder $queryBuilder, QueryNameGeneratorInterface $queryNameGenerator, string $resourceClass, array $identifiers, ?Operation $operation = null, array $context = []): void
    {
        $this->restrictQuery($queryBuilder, $resourceClass);
    }

    private function restrictQuery(QueryBuilder $queryBuilder, string $resourceClass): void
    {
        if ($resourceClass !== Payment::class) {
            return;
        }

        /** @var User $user */
        $user = $this->security->getUser();

        // If User is logged-in
        if ($user) {
            // Get the alias for the Payment entity
            $rootAlias = $queryBuilder->getRootAliases()[0];

            $queryBuilder
                ->join('App\Entity\Order', 'ord', 'WITH', "$rootAlias.order = ord")
                ->andWhere('ord.ownedBy = :user')
                ->setParameter('user', $user->getId());
        }
    }
}