<?php

declare(strict_types = 1);

namespace App\Contracts;

use App\Enum\ActivityLog;

interface ActivityLogFormatterInterface
{
    public function getDescription(ActivityLog $activityLog, ?array $context = []): string;
}