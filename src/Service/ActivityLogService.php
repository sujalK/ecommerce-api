<?php

declare(strict_types = 1);

namespace App\Service;

use App\Contracts\PersistenceServiceInterface;
use App\Entity\User;
use App\Enum\ActivityLog as ActivityLogEnum;
use App\Entity\ActivityLog;
use Symfony\Bundle\SecurityBundle\Security;

class ActivityLogService
{

    public function __construct (
        private readonly PersistenceServiceInterface $persistenceService,
        private readonly Security $security,
    )
    {
    }

    public function logActivity(ActivityLogEnum $log, ?string $description = null): void
    {
        $owner = $this->security->getUser();
        assert($owner instanceof User);
        $activityLog = new ActivityLog();

        $activityLog->setDescription($description);
        $activityLog->setActivity($log->value);
        $activityLog->setOwner($owner);

        $this->persistenceService->sync($activityLog);
    }

}