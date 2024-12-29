<?php

declare(strict_types = 1);

namespace App\Mapper\ActivityLog;

use App\ApiResource\ActivityLog\ActivityLogApi;
use App\Entity\ActivityLog;
use App\Entity\User;
use App\Repository\ActivityLogRepository;
use Symfonycasts\MicroMapper\AsMapper;
use Symfonycasts\MicroMapper\MapperInterface;
use Symfonycasts\MicroMapper\MicroMapperInterface;

#[AsMapper(from: ActivityLogApi::class, to: ActivityLog::class)]
class ActivityLogApiToEntityMapper implements MapperInterface
{

    public function __construct (
        private readonly ActivityLogRepository $repository,
        private readonly MicroMapperInterface $microMapper,
    )
    {
    }

    public function load(object $from, string $toClass, array $context): object
    {
        $dto = $from;
        assert($dto instanceof ActivityLogApi);

        $entity = $dto->id ? $this->repository->find($dto->id) : new ActivityLog();

        if ( ! $entity ) {
            throw new \Exception (
                \sprintf('Activity log with id "%d" not found', $dto->id)
            );
        }

        return $entity;
    }

    public function populate(object $from, object $to, array $context): object
    {
        $dto    = $from;
        $entity = $to;
        assert($dto instanceof ActivityLogApi);
        assert($entity instanceof ActivityLog);

        $entity->setOwner (
            $this->microMapper->map($dto->owner, User::class, [
                MicroMapperInterface::MAX_DEPTH => 0,
            ])
        );
        $entity->setActivity($dto->activity);
        $entity->setDescription($dto->description);

        return $entity;
    }
}