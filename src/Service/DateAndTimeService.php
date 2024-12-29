<?php

declare(strict_types = 1);

namespace App\Service;

use App\Contracts\DateAndTimeInterface;

class DateAndTimeService implements DateAndTimeInterface
{
    public function getTimeZone(): \DateTimeZone
    {
        return new \DateTimeZone('UTC');
    }
}