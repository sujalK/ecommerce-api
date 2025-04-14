<?php

declare(strict_types = 1);

namespace App\Security;

use App\Repository\ApiTokenRepository;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\Exception\CustomUserMessageAuthenticationException;
use Symfony\Component\Security\Http\AccessToken\AccessTokenHandlerInterface;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\UserBadge;

class ApiTokenHandler implements AccessTokenHandlerInterface
{

    public function __construct(
        private readonly ApiTokenRepository $repository,
    )
    {
    }

    public function getUserBadgeFrom(#[\SensitiveParameter] string $accessToken): UserBadge
    {
        $apiToken = $this->repository->findOneBy(['token' => $accessToken]);

        if ( ! $apiToken ) {
            throw new AuthenticationException('Invalid token');
        }

        // check for the token validity
        if ( ! $apiToken->isValid() ) {
            throw new CustomUserMessageAuthenticationException('Invalid token');
        }
        
        // get the owner from the token
        $owner = $apiToken->getOwner();

        if (!$owner->getIsActive()) {
            throw new CustomUserMessageAuthenticationException('Account is inactive. Please contact support for further instructions.');
        }

        if ($owner->getVerificationToken() !== null || !$owner->getVerifiedAt()) {
            throw new CustomUserMessageAuthenticationException('Invalid token');
        }

        // set access token scopes to user's property so that getRoles() in User entity utilizes our roles
        $owner->setAccessTokenScopes($apiToken->getScopes());

        return new UserBadge($owner->getUserIdentifier());
    }
}