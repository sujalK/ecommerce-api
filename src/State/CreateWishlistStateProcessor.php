<?php

declare(strict_types = 1);

namespace App\State;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use App\ApiResource\User\UserApi;
use App\Entity\User;
use Symfony\Bundle\SecurityBundle\Security;
use Symfonycasts\MicroMapper\MicroMapperInterface;

class CreateWishlistStateProcessor implements ProcessorInterface
{

    public function __construct (
        private readonly DtoToEntityStateProcessor $processor,
        private readonly MicroMapperInterface $microMapper,
        private readonly Security $security,
    )
    {
    }

    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = [])
    {
        /** @var User $user */
        $user = $this->security->getUser();

        // set the owner of the wishlist
        $data->ownedBy = $this->microMapper->map($user, UserApi::class, [
            MicroMapperInterface::MAX_DEPTH => 0,
        ]);

        return $this->processor->process($data, $operation, $uriVariables, $context);
    }
}
