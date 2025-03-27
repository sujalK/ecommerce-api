<?php

declare(strict_types = 1);

namespace App\State;

use ApiPlatform\Metadata\Delete;
use ApiPlatform\Metadata\Operation;
use ApiPlatform\Metadata\Patch;
use ApiPlatform\Metadata\Post;
use ApiPlatform\State\ProcessorInterface;
use App\ApiResource\Notification\NotificationApi;
use App\Entity\Notification;
use App\Enum\ActivityLog;
use App\Service\ActivityLogService;
use Doctrine\ORM\EntityManagerInterface;
use Symfonycasts\MicroMapper\MicroMapperInterface;

class NotificationStateProcessor implements ProcessorInterface
{

    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly DtoToEntityStateProcessor $processor,
        private readonly ActivityLogService $activityLogService,
        private readonly MicroMapperInterface $microMapper,
    )
    {
    }

    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = [])
    {
        assert($data instanceof NotificationApi);

        // For post request, set isRead to false even if it is sent during the request because, initially notification is not read during creation
        if ($operation instanceof Post) {
            $data->isRead = false;
        }

        // For PATCH requests, only change the read status of a notification
        if ($operation instanceof Patch) {
            // Fetch the original entity from the database
            $originalEntity = $context['previous_data'] ?? null;
            assert($originalEntity instanceof NotificationApi);

            if ($originalEntity->isRead !== $data->isRead) {
                $notificationEntity = $this->microMapper->map($originalEntity, Notification::class, [
                    MicroMapperInterface::MAX_DEPTH => 0,
                ]);
                $notificationEntity->setRead($data->isRead);

                $this->entityManager->persist($notificationEntity);
                $this->entityManager->flush();
            }

            return ['isRead' => $originalEntity->isRead !== $data->isRead ? $data->isRead : $originalEntity->isRead];
        }

        $entity = $this->processor->process($data, $operation, $uriVariables, $context);

        // store the log
        $this->log($operation, $entity);

        return $entity;
    }

    public function log(Operation $operation, mixed $entity): void
    {
        if ($operation instanceof Post) {
            $this->activityLogService->storeLog(ActivityLog::POST_NOTIFICATION, $entity);
        } else if ($operation instanceof Delete) {
            $this->activityLogService->storeLog(ActivityLog::DELETE_NOTIFICATION);
        }
    }
}
