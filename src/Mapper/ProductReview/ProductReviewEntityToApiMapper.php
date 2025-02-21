<?php

declare(strict_types = 1);

namespace App\Mapper\ProductReview;

use App\ApiResource\Product\ProductApi;
use App\ApiResource\ProductReview\ProductReviewApi;
use App\ApiResource\User\UserApi;
use App\Entity\ProductReview;
use Carbon\Carbon;
use Symfonycasts\MicroMapper\AsMapper;
use Symfonycasts\MicroMapper\MapperInterface;
use Symfonycasts\MicroMapper\MicroMapperInterface;

#[AsMapper(from: ProductReview::class, to: ProductReviewApi::class)]
class ProductReviewEntityToApiMapper implements MapperInterface
{

    public function __construct (
        private readonly MicroMapperInterface $microMapper,
    )
    {
    }

    public function load(object $from, string $toClass, array $context): object
    {
        $entity = $from;
        assert($entity instanceof ProductReview);

        $dto     = new ProductReviewApi();
        $dto->id = $entity->getId();

        return $dto;
    }

    public function populate(object $from, object $to, array $context): object
    {
        $entity = $from;
        $dto    = $to;
        assert($entity instanceof ProductReview);
        assert($dto instanceof ProductReviewApi);

        $dto->product    = $this->microMapper->map($entity->getProduct(), ProductApi::class, [
            MicroMapperInterface::MAX_DEPTH => 0,
        ]);
        
        $dto->owner      = $this->microMapper->map($entity->getOwner(), UserApi::class, [
            MicroMapperInterface::MAX_DEPTH => 0,
        ]);

        $dto->rating     = $entity->getRating();
        $dto->reviewText = $entity->getReviewText();
        $dto->createdAt  = $entity->getCreatedAt();
        $dto->updatedAt  = $entity->getUpdatedAt();

        $dto->dateCreated = Carbon::parse($entity->getCreatedAt())->diffForHumans();

        if ( $entity->getUpdatedAt() ) {
            $dto->dateUpdated = Carbon::parse($entity->getUpdatedAt())->diffForHumans();
        }
        $dto->isActive = $entity->isActive();

        return $dto;
    }
}