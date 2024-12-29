<?php

declare(strict_types = 1);

namespace App\Contracts;

interface DateAndTimeInterface
{
    public function getTimeZone(): \DateTimeZone;
}