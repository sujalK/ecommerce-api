<?php

namespace App\Mapper\Wishlist;

use App\ApiResource\Wishlist\WishlistApi;
use App\Entity\Product;
use App\Entity\User;
use App\Entity\Wishlist;
use App\Repository\WishlistRepository;
use Symfonycasts\MicroMapper\AsMapper;
use Symfonycasts\MicroMapper\MapperInterface;
use Symfonycasts\MicroMapper\MicroMapperInterface;

#[AsMapper(from: WishlistApi::class, to: Wishlist::class)]
class WishlistApiToEntityMapper implements MapperInterface
{

    public function __construct(
        private readonly WishlistRepository $repository,
        private readonly MicroMapperInterface $microMapper,
    )
    {
    }

    public function load(object $from, string $toClass, array $context): object
    {
        $dto = $from;
        assert($dto instanceof WishlistApi);

        $entity = $dto->id ? $this->repository->find($dto->id) : new Wishlist();

        if ( ! $entity ) {
            throw new \Exception(
                \sprintf('Wishlist of id "%d" not found', $dto->id)
            );
        }

        return $entity;
    }

    public function populate(object $from, object $to, array $context): object
    {
        $dto    = $from;
        $entity = $to;
        assert($dto instanceof WishlistApi);
        assert($entity instanceof Wishlist);

        $entity->setOwnedBy (
            $this->microMapper->map($dto->ownedBy, User::class, [
                MicroMapperInterface::MAX_DEPTH => 0,
            ])
        );
        $entity->setProduct (
            $this->microMapper->map($dto->product, Product::class, [
                MicroMapperInterface::MAX_DEPTH => 0,
            ])
        );

        return $entity;
    }
}