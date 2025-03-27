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
    paginationItemsPerPage: 10,
    security: 'is_granted("ROLE_USER")', // User needs to be logged-in to access this resource,
    provider: EntityToDtoStateProvider::class,
    processor: DtoToEntityStateProcessor::class,
    stateOptions: new Options(entityClass: ActivityLog::class)
)]
class ActivityLogApi
{

    #[ApiProperty(readable: true, writable: false, identifier: true)]
    public ?int $id                       = null;

    public ?UserApi $owner                = null;

    public ?string $activity              = null;

    public ?string $description           = null;

    public ?\DateTimeImmutable $createdAt = null;

    /*
     * isLoggedInAdminLog
     * This field is visible only if admin is logged-in and owner of the log is logged-in user (admin)
     */
    #[ApiProperty(writable: false, security: '(object.owner.id === user.getId()) and is_granted("ROLE_ADMIN")')]
    public ?bool $isLoggedInAdminLog = true;

    /*
     * isMine property is returned to indicate the ActivityLog belongs to the logged-in user.
     * This property is only returned if ActivityLog belongs to the logged-in user.
     */
    #[ApiProperty(writable: false, security: 'object.owner.id === user.getId()')]
    public ?bool $isMine = true;

}