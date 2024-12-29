<?php

declare(strict_types = 1);

namespace App\Mapper\Notification;

use App\ApiResource\Notification\NotificationApi;
use App\ApiResource\User\UserApi;
use App\Entity\Notification;
use Symfonycasts\MicroMapper\AsMapper;
use Symfonycasts\MicroMapper\MapperInterface;
use Symfonycasts\MicroMapper\MicroMapperInterface;

#[AsMapper(from: Notification::class, to: NotificationApi::class)]
class NotificationEntityToApiMapper implements MapperInterface
{

    public function __construct (
        private readonly MicroMapperInterface $microMapper,
    )
    {
    }

    public function load(object $from, string $toClass, array $context): object
    {
        $entity = $from;
        assert($entity instanceof Notification);

        $dto     = new NotificationApi();
        $dto->id = $entity->getId();

        return $dto;
    }

    public function populate(object $from, object $to, array $context): object
    {
        $entity = $from;
        $dto    = $to;
        assert($entity instanceof Notification);
        assert($dto instanceof NotificationApi);

        $dto->ownedBy = $this->microMapper->map($entity->getOwnedBy(), UserApi::class, [
            MicroMapperInterface::MAX_DEPTH => 0,
        ]);
        $dto->message   = $entity->getMessage();
        $dto->createdAt = $entity->getCreatedAt();
        $dto->isRead    = $entity->isRead();

        return $dto;
    }
}