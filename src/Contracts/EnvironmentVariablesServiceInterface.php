<?php

declare(strict_types = 1);

namespace App\Contracts;

use App\Enum\EnvVars;

interface EnvironmentVariablesServiceInterface
{
    public function get(EnvVars $key): string;
}