<?php

declare(strict_types = 1);

namespace App\Service;

use App\Contracts\AuthInterface;
use App\Exception\AuthenticationException;
use App\Exception\UnauthorizedException;
use Symfony\Bundle\SecurityBundle\Security;

class AuthService implements AuthInterface
{

    private const string ROLE_USER = 'ROLE_USER';

    public function __construct (
        private readonly Security $security,
    ) {
    }

    /**
     * @throws UnauthorizedException
     */
    public function checkAuthorization(string $role): void
    {
        if (!$this->security->isGranted($role)) {
            throw new UnauthorizedException();
        }
    }

    /**
     * @throws AuthenticationException
     */
    public function checkAuthentication(): void
    {
        if ( ! $this->security->getUser() ) {
            throw new AuthenticationException();
        }
    }

    /**
     * @throws UnauthorizedException
     * @throws AuthenticationException
     */
    public function authenticateAndAuthorize(string $role = self::ROLE_USER): void
    {
        $this->checkAuthentication();
        $this->checkAuthorization($role);
    }

    /**
     * @throws UnauthorizedException
     */
    public function checkRolesForAuthorization(array $roles, bool $requireAll = true): void
    {
        $grantedRoles = array_filter($roles, fn($role) => $this->security->isGranted($role));

        if (!$requireAll && count($grantedRoles) === 0) {
            throw new UnauthorizedException('None of the required roles were granted.');
        }

        if ($requireAll && count($grantedRoles) !== count($roles)) {
            throw new UnauthorizedException('Missing required roles for authorization.');
        }
    }

}