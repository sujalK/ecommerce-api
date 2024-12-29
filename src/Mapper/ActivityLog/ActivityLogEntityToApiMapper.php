<?php

declare(strict_types = 1);

namespace App\Mapper\ActivityLog;

use App\ApiResource\ActivityLog\ActivityLogApi;
use App\ApiResource\User\UserApi;
use App\Entity\ActivityLog;
use Symfonycasts\MicroMapper\AsMapper;
use Symfonycasts\MicroMapper\MapperInterface;
use Symfonycasts\MicroMapper\MicroMapperInterface;

#[AsMapper(from: ActivityLog::class, to: ActivityLogApi::class)]
class ActivityLogEntityToApiMapper implements MapperInterface
{

    public function __construct (
        private readonly MicroMapperInterface $microMapper,
    )
    {
    }

    public function load(object $from, string $toClass, array $context): object
    {
        $entity = $from;
        assert($entity instanceof ActivityLog);

        $dto     = new ActivityLogApi();
        $dto->id = $entity->getId();

        return $dto;
    }

    public function populate(object $from, object $to, array $context): object
    {

        $entity = $from;
        $dto    = $to;
        assert($entity instanceof ActivityLog);
        assert($dto instanceof ActivityLogApi);

        $dto->owner = $this->microMapper->map($entity->getOwner(), UserApi::class, [
            MicroMapperInterface::MAX_DEPTH => 0,
        ]);

        $dto->activity    = $entity->getActivity();
        $dto->description = $entity->getDescription();
        $dto->createdAt   = $entity->getCreatedAt();

        return $dto;
    }
}