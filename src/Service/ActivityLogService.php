<?php

declare(strict_types = 1);

namespace App\Service;

use App\Contracts\ActivityLogFormatterInterface;
use App\Contracts\PersistenceServiceInterface;
use App\Entity\User;
use App\Enum\ActivityLog as ActivityLogEnum;
use App\Entity\ActivityLog;
use Symfony\Bundle\SecurityBundle\Security;

class ActivityLogService
{

    public function __construct (
        private readonly PersistenceServiceInterface $persistenceService,
        private readonly ActivityLogFormatterInterface $activityLogFormatter,
        private readonly Security $security,
    )
    {
    }

    public function logActivity(ActivityLogEnum $log, ?string $description = null, ?string $data = null): void
    {
        $owner = $this->security->getUser();
        assert($owner instanceof User);
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