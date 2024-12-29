<?php

declare(strict_types = 1);

namespace App\Mapper\Notification;

use App\ApiResource\Notification\NotificationApi;
use App\Entity\Notification;
use App\Entity\User;
use App\Repository\NotificationRepository;
use Symfonycasts\MicroMapper\AsMapper;
use Symfonycasts\MicroMapper\MapperInterface;
use Symfonycasts\MicroMapper\MicroMapperInterface;

#[AsMapper(from: NotificationApi::class, to: Notification::class)]
class NotificationApiToEntityMapper implements MapperInterface
{

    public function __construct (
        private readonly NotificationRepository $repository,
        private readonly MicroMapperInterface $microMapper,
    )
    {
    }

    public function load(object $from, string $toClass, array $context): object
    {
        $dto = $from;
        assert($dto instanceof NotificationApi);

        $entity = $dto->id ? $this->repository->find($dto->id) : new Notification();

        if ( ! $entity ) {
            throw new \Exception (
                \sprintf('Notification with id "%d" not found', $dto->id)
            );
        }

        return $entity;
    }

    public function populate(object $from, object $to, array $context): object
    {
        $dto    = $from;
        $entity = $to;
        assert($dto instanceof NotificationApi);
        assert($entity instanceof Notification);

        $entity->setOwnedBy (
            $this->microMapper->map($dto->ownedBy, User::class, [
                MicroMapperInterface::MAX_DEPTH => 0,
            ])
        );
        $entity->setMessage($dto->message);
        $entity->setCreatedAt($dto->createdAt);
        $entity->setRead($dto->isRead);

        return $entity;
    }
}