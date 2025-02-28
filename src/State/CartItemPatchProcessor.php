<?php

declare(strict_types = 1);

namespace App\State;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use App\ApiResource\CartItem\CartItemApi;
use App\Enum\ActivityLog;
use App\Service\ActivityLogService;
use App\Service\Cart\CartUpdaterService;
use Doctrine\ORM\EntityNotFoundException;
use Symfony\Component\HttpFoundation\JsonResponse;

class CartItemPatchProcessor implements ProcessorInterface
{

    public function __construct (
        private readonly DtoToEntityStateProcessor $processor,
        private readonly CartUpdaterService $cartUpdaterService,
        private readonly ActivityLogService $activityLogService,
    )
    {
    }

    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = [])
    {
        $response = $this->cartUpdaterService->init($data, $this->processor, $context, $operation, $uriVariables);

        if ($response instanceof CartItemApi) {
            $this->activityLogService->storeLog(ActivityLog::UPDATE_CART_ITEM, $data);
        } else if ($response instanceof JsonResponse) {
            $this->activityLogService->storeLog(ActivityLog::UPDATE_CART_ITEM_ERROR, $data);
        }

        return $response;
    }
}
