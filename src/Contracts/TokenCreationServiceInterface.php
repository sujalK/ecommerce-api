<?php

declare(strict_types = 1);

namespace App\Contracts;

use App\Entity\ApiToken;
use App\Entity\User;

interface TokenCreationServiceInterface
{
    public function createToken(User $user): ApiToken;
}