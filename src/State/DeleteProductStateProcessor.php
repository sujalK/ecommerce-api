<?php

declare(strict_types = 1);

namespace App\State;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use App\ApiResource\Product\ProductApi;
use App\Contracts\CloudServiceProviderInterface;
use App\Contracts\HttpResponseInterface;
use App\Enum\ActivityLog;
use App\Exception\ErrorDeletingFileFromCloudStorageException;
use App\Exception\MissingObjectKeyException;
use App\Service\ActivityLogService;

class DeleteProductStateProcessor implements ProcessorInterface
{

    public function __construct (
        private readonly DtoToEntityStateProcessor $processor,
        private readonly CloudServiceProviderInterface $cloudServiceProvider,
        private readonly HttpResponseInterface $response,
        private readonly ActivityLogService $activityLogService,
    )
    {
    }

    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = [])
    {
        assert($data instanceof ProductApi);

        try {
            // Delete data
            $result = $this->processor->process($data, $operation, $uriVariables, $context);

            // Delete file from S3
            $this->cloudServiceProvider->deleteObject($data->s3FileName);

            // log
            $this->activityLogService->storeLog(ActivityLog::DELETE_PRODUCT);
        } catch (ErrorDeletingFileFromCloudStorageException $e) {
            return $this->response->serverError(description: $e->getDescription());
        }

        return $result;
    }
}
