<?php

declare(strict_types = 1);

namespace App\State;

use ApiPlatform\Metadata\Delete;
use ApiPlatform\Metadata\Operation;
use ApiPlatform\Metadata\Post;
use ApiPlatform\State\ProcessorInterface;
use App\Enum\ActivityLog;
use App\Service\ActivityLogService;

class WishlistStateProcessor implements ProcessorInterface
{

    public function __construct (
        private readonly DtoToEntityStateProcessor $processor,
        private readonly ActivityLogService $activityLogService,
    )
    {
    }

    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = [])
    {

        $entity = $this->processor->process($data, $operation, $uriVariables, $context);

        $this->log($operation, $entity, $uriVariables['id'] ?? null);

        return $entity;
    }

    public function log(Operation $operation, mixed $entity, ?int $id = null): void
    {
        if ($operation instanceof Post) {
            $this->activityLogService->storeLog(ActivityLog::CREATE_WISHLIST, $entity);
        } else if ($operation instanceof Delete) {
            $object       = new \stdClass();
            $object->id ??= $id;

            $this->activityLogService->storeLog(ActivityLog::DELETE_WISHLIST, $object);
        }
    }
}
