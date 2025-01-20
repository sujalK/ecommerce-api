<?php

declare(strict_types = 1);

namespace App\Contracts;

interface AuthInterface
{

    public function checkAuthentication(): void;

    public function checkAuthorization(string $role): void;

    public function authenticateAndAuthorize(string $role = 'ROLE_USER'): void;

    public function checkRolesForAuthorization(array $roles, bool $requireAll = true): void;

}