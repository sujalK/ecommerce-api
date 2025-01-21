<?php

declare(strict_types = 1);

namespace App\State;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use App\ApiResource\CartItem\CartItemApi;
use App\Contracts\HttpResponseInterface;
use App\Entity\CartItem;
use App\Enum\ActivityLog;
use App\Service\ActivityLogService;
use Doctrine\ORM\EntityManagerInterface;

class DeleteCartItemProcessor implements ProcessorInterface
{

    public function __construct(
        private readonly ActivityLogService $activityLogService,
        private readonly DtoToEntityStateProcessor $processor,
    )
    {
    }

    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = [])
    {
        assert($data instanceof CartItemApi);

        // log the user activity
        $this->activityLogService->logActivity (
            log: ActivityLog::DELETE_CART_ITEM,
            description: ActivityLog::DELETE_CART_ITEM->getDeleteCartItemDescription($data->product->id)
        );

        return $this->processor->process($data, $operation, $uriVariables, $context);
    }
}
