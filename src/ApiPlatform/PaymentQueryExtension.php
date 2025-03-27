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
use Symfony\Component\HttpFoundation\RequestStack;

class PaymentQueryExtension implements QueryCollectionExtensionInterface, QueryItemExtensionInterface
{

    public function __construct (
        private readonly Security $security,
        private readonly RequestStack $requestStack,
    )
    {
    }

    public function applyToCollection(QueryBuilder $queryBuilder, QueryNameGeneratorInterface $queryNameGenerator, string $resourceClass, ?Operation $operation = null, array $context = []): void
    {
        $this->restrictQuery($queryBuilder, $resourceClass, $context);
    }

    public function applyToItem(QueryBuilder $queryBuilder, QueryNameGeneratorInterface $queryNameGenerator, string $resourceClass, array $identifiers, ?Operation $operation = null, array $context = []): void
    {
        $this->restrictQuery($queryBuilder, $resourceClass);
    }

    private function restrictQuery(QueryBuilder $queryBuilder, string $resourceClass, array $context = []): void
    {
        if ($resourceClass !== Payment::class) {
            return;
        }

        if ($this->security->isGranted('ROLE_ADMIN')) {
            return;
        }

        /** @var User $user */
        $user = $this->security->getUser();

        $operationString = $context['request']->attributes->all()['_api_operation_name'];
        if (str_ends_with($operationString, '_get_collection')) {
            $this->filterQuery($queryBuilder, $user);
            return;
        }

        $request = $this->requestStack->getCurrentRequest();
        if ($request && $request->getRealMethod() === 'GET') {
            return;
        }

        // If User is logged-in
        if ($user) {
            $this->filterQuery($queryBuilder, $user);
        }
    }

    public function filterQuery(QueryBuilder $queryBuilder, User $user): void
    {
        // Get the alias for the Payment entity
        $rootAlias = $queryBuilder->getRootAliases()[0];

        $queryBuilder
            ->join('App\Entity\Order', 'ord', 'WITH', "$rootAlias.order = ord")
            ->andWhere('ord.ownedBy = :user')
            ->setParameter('user', $user->getId());
    }
}