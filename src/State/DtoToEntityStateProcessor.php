<?php

declare(strict_types = 1);

namespace App\State;

use ApiPlatform\Doctrine\Common\State\PersistProcessor;
use ApiPlatform\Doctrine\Common\State\RemoveProcessor;
use ApiPlatform\Doctrine\Orm\State\Options;
use ApiPlatform\Metadata\DeleteOperationInterface;
use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfonycasts\MicroMapper\MicroMapperInterface;

class DtoToEntityStateProcessor implements ProcessorInterface
{

    public function __construct (
        #[Autowire(service: PersistProcessor::class)] private readonly ProcessorInterface $processor,
        #[Autowire(service: RemoveProcessor::class)] private readonly ProcessorInterface $removeProcessor,
        private readonly MicroMapperInterface $microMapper,
    )
    {
    }

    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = [])
    {
        $options = $operation->getStateOptions();
        assert($options instanceof Options);
        
        // get the entityClass
        $entityClass = $options->getEntityClass();

        // get the entity from the dto
        $entity   = $this->mapDtoToEntity($data, $entityClass);

        if ( $operation instanceof DeleteOperationInterface) {
            $this->removeProcessor->process($entity, $operation, $uriVariables, $context);

            return null; // because we don't return anything on DELETE operation, so null here does work of that, returning nothing
        }

        $this->processor->process($entity, $operation, $uriVariables, $context);
        $data->id = $entity->getId();

        return $data;
    }

    private function mapDtoToEntity(mixed $data, string $entityClass)
    {
        return $this->microMapper->map($data, $entityClass);
    }
}
