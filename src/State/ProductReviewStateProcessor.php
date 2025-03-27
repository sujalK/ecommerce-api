<?php

declare(strict_types = 1);

namespace App\State;

use ApiPlatform\Metadata\Delete;
use ApiPlatform\Metadata\Operation;
use ApiPlatform\Metadata\Patch;
use ApiPlatform\Metadata\Post;
use ApiPlatform\State\ProcessorInterface;
use App\ApiResource\ProductReview\ProductReviewApi;
use App\ApiResource\User\UserApi;
use App\Entity\User;
use App\Enum\ActivityLog;
use App\Service\ActivityLogService;
use Symfony\Bundle\SecurityBundle\Security;
use Symfonycasts\MicroMapper\MicroMapperInterface;

class ProductReviewStateProcessor implements ProcessorInterface
{

    public function __construct (
        private readonly ActivityLogService $activityLogService,
        private readonly DtoToEntityStateProcessor $processor,
        private readonly MicroMapperInterface $microMapper,
        private readonly Security $security,
    )
    {
    }

    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = [])
    {
        assert($data instanceof ProductReviewApi);

        /** @var User $user */
        $user = $this->security->getUser();

        if ($operation instanceof Patch) {
            // set the updatedAt
            $data->updatedAt = new \DateTimeImmutable('now', new \DateTimeZone('UTC'));

            // make inactive after edit review, so that admin can again approve the review.
            $data->isActive = false;
        }

        if ($operation instanceof Post) {
            // set the owner
            $data->owner = $this->microMapper->map($user, UserApi::class, [
                MicroMapperInterface::MAX_DEPTH => 0,
            ]);
        }

        $entity = $this->processor->process($data, $operation, $uriVariables, $context);

        // log
        $this->log($operation, $entity);

        return $entity;
    }

    public function log(Operation $operation, mixed $entity): void
    {
        if ($operation instanceof Post) {
            $this->activityLogService->storeLog(ActivityLog::CREATE_PRODUCT_REVIEW, $entity);
        } else if ($operation instanceof Patch) {
            $this->activityLogService->storeLog(ActivityLog::UPDATE_PRODUCT_REVIEW, $entity);
        } else if ($operation instanceof Delete) {
            $this->activityLogService->storeLog(ActivityLog::DELETE_PRODUCT_REVIEW);
        }
    }
}
