<?php

declare(strict_types = 1);

namespace App\State;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use App\ApiResource\Product\ProductApi;
use App\Contracts\EnvironmentVariablesServiceInterface;
use App\Contracts\RequestDataUtilsInterface;
use App\Enum\ActivityLog;
use App\Enum\EnvVars;
use App\Service\ActivityLogService;

class ProductInfoStateProcessor implements ProcessorInterface
{

    public function __construct (
        private readonly DtoToEntityStateProcessor $processor,
        private readonly RequestDataUtilsInterface $requestDataUtils,
        private readonly EnvironmentVariablesServiceInterface $envVarsService,
        private readonly ActivityLogService $activityLogService,
    )
    {
    }

    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = [])
    {
        assert($data instanceof ProductApi);

        // set up the updatedAt date and time
        $this->requestDataUtils->setUpdatedAt($data);

        $this->processor->process($data, $operation, $uriVariables, $context);

        // set the productImage, in response of update
        $data->productImage = $this->getProductImage($data);

        // log
        $this->activityLogService->storeLog(ActivityLog::UPDATE_PRODUCT_INFO);

        return $data;
    }

    /**
     * Returns the product image URL
     *
     * @param ProductApi $object
     * @return string
     */
    private function getProductImage(ProductApi $object): string
    {
        return sprintf (
            "https://%s.s3.%s.amazonaws.com/%s",
            $this->envVarsService->get(EnvVars::BUCKET_NAME),
            $this->envVarsService->get(EnvVars::REGION),
            $object->s3FileName
        );
    }

}
