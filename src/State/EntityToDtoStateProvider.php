<?php

declare(strict_types = 1);

namespace App\State;

use ApiPlatform\Doctrine\Orm\Paginator;
use ApiPlatform\Doctrine\Orm\State\CollectionProvider;
use ApiPlatform\Doctrine\Orm\State\ItemProvider;
use ApiPlatform\Metadata\CollectionOperationInterface;
use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\Pagination\TraversablePaginator;
use ApiPlatform\State\ProviderInterface;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfonycasts\MicroMapper\MicroMapperInterface;

class EntityToDtoStateProvider implements ProviderInterface
{

    public function __construct (
        #[Autowire(service: CollectionProvider::class)] private readonly ProviderInterface $provider,
        #[Autowire(service: ItemProvider::class)] private readonly ProviderInterface $itemProvider,
        private readonly MicroMapperInterface $microMapper,
    )
    {
    }

    public function provide(Operation $operation, array $uriVariables = [], array $context = []): object|array|null
    {
        $resourceClass = $context['resource_class'];

        if ( $operation instanceof CollectionOperationInterface ) {
            $paginator = $this->provider->provide($operation, $uriVariables, $context);
            assert($paginator instanceof Paginator);

            $dtos = [];
            foreach ( $paginator as $entity ) {
                 $dtos[] = $this->mapEntityToDto($entity, $resourceClass);
            }

            return new TraversablePaginator (
                new \ArrayIterator($dtos),
                $paginator->getCurrentPage(),
                $paginator->getItemsPerPage(),
                $paginator->getTotalItems(),
            );
        }

        $entity = $this->itemProvider->provide($operation, $uriVariables, $context);

        if ( ! $entity ) {
            return null; // returning null from here means that 404 page is triggered, if the item is not found
        }

        return $this->mapEntityToDto($entity, $resourceClass);
    }

    private function mapEntityToDto(object $entity, string $resourceClass): object
    {
        return $this->microMapper->map($entity, $resourceClass);
    }
}
