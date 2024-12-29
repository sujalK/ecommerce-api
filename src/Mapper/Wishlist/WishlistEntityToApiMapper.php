<?php

declare(strict_types = 1);

namespace App\Mapper\Wishlist;

use App\ApiResource\Product\ProductApi;
use App\ApiResource\User\UserApi;
use App\ApiResource\Wishlist\WishlistApi;
use App\Entity\Wishlist;
use App\Repository\WishlistRepository;
use Symfonycasts\MicroMapper\AsMapper;
use Symfonycasts\MicroMapper\MapperInterface;
use Symfonycasts\MicroMapper\MicroMapperInterface;

#[AsMapper(from: Wishlist::class, to: WishlistApi::class)]
class WishlistEntityToApiMapper implements MapperInterface
{
    
    public function __construct (
        private readonly MicroMapperInterface $microMapper,
    )
    {
    }

    public function load(object $from, string $toClass, array $context): object
    {
        $entity = $from;
        assert($entity instanceof Wishlist);

        $dto     = new WishlistApi();
        $dto->id = $entity->getId();

        return $dto;
    }

    public function populate(object $from, object $to, array $context): object
    {
        $entity = $from;
        $dto    = $to;
        assert($entity instanceof Wishlist);
        assert($dto instanceof WishlistApi);

        $dto->ownedBy = $this->microMapper->map($entity->getOwnedBy(), UserApi::class, [
            MicroMapperInterface::MAX_DEPTH => 0,
        ]);
        $dto->product = $this->microMapper->map($entity->getProduct(), ProductApi::class, [
            MicroMapperInterface::MAX_DEPTH => 0,
        ]);

        return $dto;
    }
}