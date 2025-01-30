<?php

declare(strict_types = 1);

namespace App\Service;

use App\Contracts\DateAndTimeInterface;
use DateTimeImmutable;

class DateAndTimeService implements DateAndTimeInterface
{
    public function getTimeZone(): \DateTimeZone
    {
        return new \DateTimeZone('UTC');
    }

    public function getCurrentDateAndTime(): DateTimeImmutable
    {
        return new DateTimeImmutable('now', $this->getTimeZone());
    }
}