<?php

declare(strict_types = 1);

namespace App\Mapper\User;

use App\ApiResource\User\UserApi;
use App\Entity\User;
use Symfonycasts\MicroMapper\AsMapper;
use Symfonycasts\MicroMapper\MapperInterface;

#[AsMapper(from: User::class, to: UserApi::class)]
class UserEntityToApiMapper implements MapperInterface
{

    public function load(object $from, string $toClass, array $context): object
    {
        $entity = $from;
        assert($entity instanceof User);

        $dto     = new UserApi();
        $dto->id = $entity->getId();

        return $dto;
    }

    public function populate(object $from, object $to, array $context): object
    {
        $entity = $from;
        $dto    = $to;

        assert($entity instanceof User);
        assert($dto instanceof UserApi);

        // map to the dto
        $dto->email               = $from->getEmail();
        $dto->userName            = $from->getUsername();
        $dto->firstName           = $from->getFirstName();
        $dto->lastName            = $from->getLastName();
        $dto->accountActiveStatus = $from->getAccountActiveStatus();
        $dto->verificationStatus  = $from->getVerificationStatus();

//        $dto->carts               = $entity->getCarts();
//        $dto->shippingAddresses   = $entity->getShippingAddresses();
//        $dto->orders              = $entity->getOrders();

        return $dto;
    }
}