<?php

declare(strict_types = 1);

namespace App\Service;

use App\Contracts\EnvironmentVariablesServiceInterface;
use App\Enum\EnvVars;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;

class EnvironmentVariablesService implements EnvironmentVariablesServiceInterface
{

    public function __construct (
        private readonly ParameterBagInterface $parameterBag,
    )
    {
    }

    public function get(EnvVars $key): string
    {
        return $this->parameterBag->get($key->value);
    }

}