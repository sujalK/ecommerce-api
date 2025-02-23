<?php

declare(strict_types = 1);

namespace App\State;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\Metadata\Post;
use ApiPlatform\State\ProcessorInterface;
use App\ApiResource\Notification\NotificationApi;

class NotificationStateProcessor implements ProcessorInterface
{

    public function __construct(
        private readonly DtoToEntityStateProcessor $processor,
    )
    {
    }

    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = [])
    {
        assert($data instanceof NotificationApi);

        // For post request, set isRead to NULL even if it is sent during the request
        if ($operation instanceof Post) {
            $data->isRead = null;
        }

        return $this->processor->process($data, $operation, $uriVariables, $context);
    }
}
