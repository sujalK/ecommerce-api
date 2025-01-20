<?php

declare(strict_types = 1);

namespace App\Mapper\User;

use App\ApiResource\User\UserApi;
use App\Entity\User;
use App\Repository\UserRepository;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfonycasts\MicroMapper\AsMapper;
use Symfonycasts\MicroMapper\MapperInterface;

#[AsMapper(from: UserApi::class, to: User::class)]
class UserApiToEntityMapper implements MapperInterface
{

    public function __construct (
        private readonly UserRepository $repository,
        private readonly UserPasswordHasherInterface $userPasswordHasher,
    )
    {
    }

    public function load(object $from, string $toClass, array $context): object
    {
        $dto = $from;
        assert($dto instanceof UserApi);

        $entity = $dto->id ? $this->repository->find($dto->id) : new User();

        if ( ! $entity ) {
            throw new \Exception (
                \sprintf('User with id "%d" not found', $dto->id)
            );
        }

        return $entity;
    }

    public function populate(object $from, object $to, array $context): object
    {
        $dto    = $from;
        $entity = $to;
        assert($dto instanceof UserApi);
        assert($entity instanceof User);

        $entity->setEmail($dto->email);

        if ( $dto->password ) {
            $entity->setPassword (
                $this->userPasswordHasher->hashPassword($entity, $dto->password)
            );
        }

        $entity->setUsername($dto->userName);
        $entity->setFirstName($dto->firstName);
        $entity->setLastName($dto->lastName);
        $entity->setAccountActiveStatus($dto->accountActiveStatus);
        $entity->setVerificationStatus($dto->verificationStatus);

        // TODO: $carts, $shippingAddresses, $orders

        return $entity;
    }
}