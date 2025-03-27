<?php

declare(strict_types = 1);

namespace App\State;

use ApiPlatform\Metadata\Delete;
use ApiPlatform\Metadata\Operation;
use ApiPlatform\Metadata\Post;
use ApiPlatform\State\ProcessorInterface;
use App\ApiResource\User\UserApi;
use App\ApiResource\Wishlist\WishlistApi;
use App\Entity\Wishlist;
use App\Enum\ActivityLog;
use App\Service\ActivityLogService;
use Symfony\Bundle\SecurityBundle\Security;
use Symfonycasts\MicroMapper\MicroMapperInterface;

class WishlistStateProcessor implements ProcessorInterface
{

    public function __construct (
        private readonly DtoToEntityStateProcessor $processor,
        private readonly ActivityLogService $activityLogService,
        private readonly MicroMapperInterface $microMapper,
        private readonly Security $security,
    )
    {
    }

    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = [])
    {

        assert($data instanceof WishlistApi);

        $user = $this->security->getUser();

        $data->ownedBy = $this->microMapper->map($user, UserApi::class, [
            MicroMapperInterface::MAX_DEPTH => 0,
        ]);

        $entity = $this->processor->process($data, $operation, $uriVariables, $context);

        $this->log($operation, $entity, $uriVariables['id'] ?? null);

        return $entity;
    }

    public function log(Operation $operation, mixed $entity, ?int $id = null): void
    {
        if ($operation instanceof Post) {
            $this->activityLogService->storeLog(ActivityLog::CREATE_WISHLIST, $entity);
        } else if ($operation instanceof Delete) {
            $object       = new \stdClass();
            $object->id ??= $id;

            $this->activityLogService->storeLog(ActivityLog::DELETE_WISHLIST, $object);
        }
    }
}
