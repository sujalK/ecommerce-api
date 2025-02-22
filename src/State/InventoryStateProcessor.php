<?php

declare(strict_types = 1);

namespace App\State;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use App\ApiResource\Inventory\InventoryApi;

class InventoryStateProcessor implements ProcessorInterface
{

    public function __construct (
        private readonly DtoToEntityStateProcessor $processor,
    )
    {
    }

    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = [])
    {
        assert($data instanceof InventoryApi);

        // Check if the current operation is POST operation
        if ( str_ends_with($operation->getName(), '_post') ) {
            // set to quantityBackOrdered to 0 during setting up/creating the inventory
            $data->quantityBackOrdered = 0;
        }

        return $this->processor->process($data, $operation, $uriVariables, $context);
    }
}
