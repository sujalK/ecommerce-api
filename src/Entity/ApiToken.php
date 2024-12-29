<?php

declare(strict_types = 1);

namespace App\Entity;

use App\Repository\ApiTokenRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ApiTokenRepository::class)]
class ApiToken
{
    // Personal access token
    public const ECOMMERCE_PERSONAL_ACCESS_TOKEN_PREFIX = 'ecp_'; // ecp is shortName for the E-Commerce personalAccessToken

    // User-specific roles
    public const SCOPE_USER_EDIT   = 'ROLE_USER_EDIT';
    public const SCOPE_USER_DELETE = 'ROLE_USER_DELETE';
    public const SCOPE_USER_FETCH  = 'ROLE_USER_FETCH';

    // description (lookup table)
    public const SCOPES = [
        self::SCOPE_USER_EDIT   => 'Edit User',
        self::SCOPE_USER_DELETE => 'Delete User',
        self::SCOPE_USER_FETCH  => 'Fetch User',
    ];

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'apiTokens')]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $owner = null;

    #[ORM\Column(length: 68)]
    private string $token;

    #[ORM\Column]
    private array $scopes = [];

    #[ORM\Column]
    private ?\DateTimeImmutable $createdAt = null;

    #[ORM\Column(nullable: true)]
    private ?\DateTimeImmutable $expiresAt = null;

    public function __construct(string $accessTokenTypePrefix = self::ECOMMERCE_PERSONAL_ACCESS_TOKEN_PREFIX)
    {
        $this->createdAt = new \DateTimeImmutable('now', new \DateTimeZone('UTC'));
        $this->token     = $accessTokenTypePrefix.bin2hex(random_bytes(32));
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getOwner(): ?User
    {
        return $this->owner;
    }

    public function setOwner(?User $owner): static
    {
        $this->owner = $owner;

        return $this;
    }

    public function getToken(): ?string
    {
        return $this->token;
    }

    public function setToken(string $token): static
    {
        $this->token = $token;

        return $this;
    }

    public function getScopes(): array
    {
        return $this->scopes;
    }

    public function setScopes(array $scopes): static
    {
        $this->scopes = $scopes;

        return $this;
    }

    public function getCreatedAt(): ?\DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTimeImmutable $createdAt): static
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    public function getExpiresAt(): ?\DateTimeImmutable
    {
        return $this->expiresAt;
    }

    public function setExpiresAt(?\DateTimeImmutable $expiresAt): static
    {
        $this->expiresAt = $expiresAt;

        return $this;
    }

    public function isValid(): bool
    {
        return $this->expiresAt === null || $this->expiresAt > new \DateTimeImmutable('now', new \DateTimeZone('UTC'));
    }
}
