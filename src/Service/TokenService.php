<?php

declare(strict_types = 1);

namespace App\Service;

use App\Contracts\DateAndTimeInterface;
use App\Contracts\TokenCreationServiceInterface;
use App\Entity\ApiToken;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;

class TokenService implements TokenCreationServiceInterface
{

    public function __construct (
        private readonly EntityManagerInterface $entityManager,
        private readonly DateAndTimeInterface $dateTime,
    )
    {
    }

    public function createToken(User $user): ApiToken
    {
        $token = new ApiToken();

        $token->setScopes(['ROLE_FULL_USER']);
        $token->setCreatedAt(new \DateTimeImmutable('now', $this->dateTime->getTimeZone()));
        $token->setExpiresAt(new \DateTimeImmutable('+2 days', $this->dateTime->getTimeZone()));
        $token->setOwner($user);

        $this->entityManager->persist($token);
        $this->entityManager->flush();

        return $token;
    }
}