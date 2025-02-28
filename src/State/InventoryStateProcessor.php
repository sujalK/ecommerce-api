<?php

declare(strict_types = 1);

namespace App\State;

use ApiPlatform\Metadata\Delete;
use ApiPlatform\Metadata\Operation;
use ApiPlatform\Metadata\Patch;
use ApiPlatform\Metadata\Post;
use ApiPlatform\State\ProcessorInterface;
use App\ApiResource\Inventory\InventoryApi;
use App\Enum\ActivityLog;
use App\Service\ActivityLogService;

class InventoryStateProcessor implements ProcessorInterface
{

    public function __construct (
        private readonly DtoToEntityStateProcessor $processor,
        private readonly ActivityLogService $activityLogService
    )
    {
    }

    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = [])
    {
        assert($data instanceof InventoryApi);

        // Check if the current operation is POST operation
        if ( $operation instanceof Post ) {
            // set to quantityBackOrdered to 0 during setting up/creating the inventory
            $data->quantityBackOrdered = 0;
        }

        $entity = $this->processor->process($data, $operation, $uriVariables, $context);

        // log the activity
        $this->log($operation, $entity);

        return $entity;
    }

    public function log(Operation $operation, mixed $entity): void
    {
        if ($operation instanceof Post) {
            $this->activityLogService->storeLog(ActivityLog::ADD_TO_INVENTORY, $entity);
        } else if ($operation instanceof Patch) {
            $this->activityLogService->storeLog(ActivityLog::UPDATE_INVENTORY_ITEM, $entity);
        } else if ($operation instanceof Delete) {
            $this->activityLogService->storeLog(ActivityLog::DELETE_INVENTORY_ITEM);
        }
    }
}
