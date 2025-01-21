<?php

declare(strict_types = 1);

namespace App\Service;

use App\Contracts\PersistenceServiceInterface;
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
        $activityLog = new ActivityLog();

        $activityLog->setDescription($description);
        $activityLog->setActivity($log->value);
        $activityLog->setOwner($this->security->getUser());

        $this->persistenceService->sync($activityLog);
    }

}