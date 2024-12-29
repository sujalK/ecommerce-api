<?php

declare(strict_types = 1);

namespace App\Mapper\ProductReview;

use App\ApiResource\ProductReview\ProductReviewApi;
use App\Entity\Product;
use App\Entity\ProductReview;
use App\Entity\User;
use App\Repository\ProductReviewRepository;
use Symfonycasts\MicroMapper\AsMapper;
use Symfonycasts\MicroMapper\MapperInterface;
use Symfonycasts\MicroMapper\MicroMapperInterface;

#[AsMapper(from: ProductReviewApi::class, to: ProductReview::class)]
class ProductReviewApiToEntityMapper implements MapperInterface
{

    public function __construct (
        private readonly ProductReviewRepository $repository,
        private readonly MicroMapperInterface $microMapper,
    )
    {
    }

    public function load(object $from, string $toClass, array $context): object
    {
        $dto = $from;
        assert($dto instanceof ProductReviewApi);

        $entity = $dto->id ? $this->repository->find($dto->id) : new ProductReview();

        if ( ! $entity ) {
            throw new \Exception (
                \sprintf('Product Review with id "%d" not found', $dto->id)
            );
        }

        return $entity;
    }

    public function populate(object $from, object $to, array $context): object
    {
        $dto    = $from;
        $entity = $to;
        assert($dto instanceof ProductReviewApi);
        assert($entity instanceof ProductReview);

        $entity->setProduct (
            $this->microMapper->map($dto->product, Product::class, [
                MicroMapperInterface::MAX_DEPTH => 0,
            ])
        );
        $entity->setOwner (
            $this->microMapper->map($dto->owner, User::class, [
                MicroMapperInterface::MAX_DEPTH => 0,
            ])
        );
        $entity->setRating($dto->rating);
        $entity->setReviewText($dto->reviewText);

        // if updatedAt is sent to us
        if ( $dto->updatedAt ) {
            $entity->setCreatedAt($dto->updatedAt);
        }

        return $entity;
    }
}