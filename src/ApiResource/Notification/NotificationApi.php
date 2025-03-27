<?php

declare(strict_types = 1);

namespace App\ApiResource\Notification;

use ApiPlatform\Doctrine\Orm\Filter\BooleanFilter;
use ApiPlatform\Doctrine\Orm\State\Options;
use ApiPlatform\Metadata\ApiFilter;
use ApiPlatform\Metadata\ApiProperty;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Delete;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Patch;
use ApiPlatform\Metadata\Post;
use App\ApiResource\User\UserApi;
use App\Entity\Notification;
use App\State\EntityToDtoStateProvider;
use App\State\NotificationStateProcessor;
use App\Validator\IsBoolean;
use App\Validator\IsValidUser;
use Symfony\Component\Validator\Constraints as Assert;

#[ApiResource (
    shortName: 'Notification',
    description: 'Notification inside the system',
    operations: [
        new Get (
            security: 'is_granted("VIEW", object)'
        ),
        new GetCollection(),
        new Patch (
            security: 'is_granted("EDIT", object)'
        ),
        new Post(
            security: 'is_granted("ROLE_ADMIN")',
            validationContext: ['groups' => ['Default', 'postValidation']],
        ),
        new Delete (
            security: "is_granted('ROLE_ADMIN')",
        ),
    ],
    paginationItemsPerPage: 10,
    security: 'is_granted("ROLE_USER")',
    provider: EntityToDtoStateProvider::class,
    processor: NotificationStateProcessor::class,
    stateOptions: new Options(entityClass: Notification::class)
)]
class NotificationApi
{

    #[ApiProperty(readable: true, writable: false, identifier: true)]
    public ?int $id                       = null;

    #[IsValidUser]
    #[Assert\NotBlank(groups: ['postValidation'])]
    public ?UserApi $ownedBy              = null;

    #[Assert\NotBlank(groups: ['postValidation'])]
    #[Assert\Regex(
        pattern: '/^[\p{L}\p{N}\s.,!?\'"-]+$/u',
        message: 'The message can only contain letters, numbers, spaces, and basic punctuation.'
    )]
    public ?string $message               = null;

    #[ApiProperty(readable: false, writable: false)]
    public ?\DateTimeImmutable $createdAt = null;

    #[IsBoolean]
    #[ApiFilter(BooleanFilter::class)]
    public ?bool $isRead                  = null;

}