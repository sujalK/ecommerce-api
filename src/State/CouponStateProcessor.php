<?php

declare(strict_types = 1);

namespace App\State;

use ApiPlatform\Metadata\Delete;
use ApiPlatform\Metadata\Operation;
use ApiPlatform\Metadata\Patch;
use ApiPlatform\Metadata\Post;
use ApiPlatform\State\ProcessorInterface;
use App\Enum\ActivityLog;
use App\Service\ActivityLogService;

class CouponStateProcessor implements ProcessorInterface
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

        // log the activity
        $this->log($operation);

        return $entity;
    }

    public function log(Operation $operation, ?object $entity = null): void
    {
        if ( $operation instanceof Post ) {
            $this->activityLogService->storeLog(ActivityLog::POST_COUPON, $entity);
        } else if ( $operation instanceof Patch ) {
            $this->activityLogService->storeLog(ActivityLog::UPDATE_COUPON, $entity);
        } else if ( $operation instanceof Delete ) {
            $this->activityLogService->storeLog(ActivityLog::DELETE_COUPON);
        }
    }
}
