<?php

declare(strict_types = 1);

namespace App\ApiResource\ActivityLog;

use ApiPlatform\Doctrine\Orm\State\Options;
use ApiPlatform\Metadata\ApiProperty;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use App\ApiResource\User\UserApi;
use App\Entity\ActivityLog;
use App\State\DtoToEntityStateProcessor;
use App\State\EntityToDtoStateProvider;

#[ApiResource (
    shortName: 'ActivityLog',
    description: 'Activity log of a user',
    operations: [
        new Get (
            security: 'is_granted("ACTIVITY_LOG_VIEW", object)'
        ),
        new GetCollection (),
    ],
    formats: [
        'json'   => ['application/json'],
        'jsonld' => ['application/ld+json']
    ],
    paginationItemsPerPage: 5,
    security: 'is_granted("ROLE_USER")', // User needs to be logged-in to access this resource,
    provider: EntityToDtoStateProvider::class,
    processor: DtoToEntityStateProcessor::class,
    stateOptions: new Options(entityClass: ActivityLog::class)
)]
class ActivityLogApi
{

    #[ApiProperty(readable: false, writable: false, identifier: true)]
    public ?int $id                       = null;

    public ?UserApi $owner                = null;

    public ?string $activity              = null;

    public ?string $description           = null;

    public ?\DateTimeImmutable $createdAt = null;

}