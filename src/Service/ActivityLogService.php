<?php

declare(strict_types = 1);

namespace App\Service;

use App\ApiResource\User\UserApi;
use App\Contracts\ActivityLogFormatterInterface;
use App\Contracts\PersistenceServiceInterface;
use App\Entity\User;
use App\Enum\ActivityLog as ActivityLogEnum;
use App\Entity\ActivityLog;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfonycasts\MicroMapper\MicroMapperInterface;

class ActivityLogService
{

    public function __construct (
        private readonly PersistenceServiceInterface $persistenceService,
        private readonly ActivityLogFormatterInterface $activityLogFormatter,
        private readonly MicroMapperInterface $microMapper,
        private readonly RequestStack $requestStack,
        private readonly Security $security,
    )
    {
    }

    public function logActivity(ActivityLogEnum $log, ?string $description = null, $data = null): void
    {
        if ($this->security->getUser()) {
            $owner = $this->security->getUser();
            assert($owner instanceof User);
        } else {
            assert($data instanceof UserApi);
            $owner = $this->microMapper->map($data, User::class, [
                MicroMapperInterface::MAX_DEPTH => 0,
            ]);
            assert($owner instanceof User);
        }

        // check if data is not string and also if data is an object
        if (!is_string($data) && is_object($data)) {
            $data = json_encode($data);
        }

        $activityLog = new ActivityLog();

        $activityLog->setDescription($description);
        $activityLog->setActivity($log->value);
        $activityLog->setData (
            $data ? json_decode($data, true) : []
        );
        $activityLog->setOwner($owner);

        $this->persistenceService->sync($activityLog);
    }

    public function storeLog(ActivityLogEnum $log, ?object $object = null): void
    {
        $object ??= new \stdClass();
        
        $description = $this->activityLogFormatter->getDescription(
            activityLog: $log,
            context: ['id' => $object->id ?? null]
        );

        // log the activity (save to database)
        $this->logActivity($log, $description, json_encode($object));
    }

}