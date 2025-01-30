<?php

declare(strict_types = 1);

namespace App\Contracts;

use DateTimeImmutable;

interface DateAndTimeInterface
{
    public function getTimeZone(): \DateTimeZone;

    public function getCurrentDateAndTime(): DateTimeImmutable;
}