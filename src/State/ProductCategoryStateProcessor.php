<?php

declare(strict_types = 1);

namespace App\State;

use ApiPlatform\Metadata\Delete;
use ApiPlatform\Metadata\Operation;
use ApiPlatform\Metadata\Patch;
use ApiPlatform\Metadata\Post;
use ApiPlatform\State\ProcessorInterface;
use App\ApiResource\ProductCategory\ProductCategoryApi;
use App\Enum\ActivityLog;
use App\Exception\DuplicateProductNameException;
use App\Mapper\ProductCategory\ProductCategoryApiToEntityMapper;
use App\Repository\ProductCategoryRepository;
use App\Service\ActivityLogService;

class ProductCategoryStateProcessor implements ProcessorInterface
{

    public function __construct(
        private readonly DtoToEntityStateProcessor $processor,
        private readonly ActivityLogService $activityLogService,
    )
    {
    }

    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = [])
    {
        assert($data instanceof ProductCategoryApi);

        $entity = $this->processor->process($data, $operation, $uriVariables, $context);

        // log
        $this->log($operation, $entity);

        return $entity;
    }

    public function log(Operation $operation, mixed $entity): void
    {
        if ($operation instanceof Post) {
            $this->activityLogService->storeLog(ActivityLog::CREATE_PRODUCT_CATEGORY, $entity);
        } else if ($operation instanceof Patch) {
            $this->activityLogService->storeLog(ActivityLog::UPDATE_PRODUCT_CATEGORY, $entity);
        } else if ($operation instanceof Delete) {
            $this->activityLogService->storeLog(ActivityLog::DELETE_PRODUCT_CATEGORY);
        }
    }
}
