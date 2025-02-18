<?php

declare(strict_types = 1);

namespace App\State;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use App\ApiResource\User\UserApi;
use App\Entity\User;
use App\Exception\MaxShippingAddressReachedException;
use App\Repository\ShippingAddressRepository;
use Symfony\Bundle\SecurityBundle\Security;
use Symfonycasts\MicroMapper\MicroMapperInterface;

class ShippingAddressStateProcessor implements ProcessorInterface
{

    public function __construct(
        private readonly ShippingAddressRepository $shippingAddressRepository,
        private readonly MicroMapperInterface $microMapper,
        private readonly DtoToEntityStateProcessor $processor,
        private readonly Security $security,
    )
    {
    }

    /**
     * @throws MaxShippingAddressReachedException The exception thrown is handled in the ApiExceptionListener
     */
    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = [])
    {
        $user = $this->security->getUser();
        assert($user instanceof User);

        if (str_ends_with($operation->getName(), '_post')) {
            // get the shipping address by the owner
            $shippingAddresses = $this->shippingAddressRepository->findBy(['owner' => $user]);

            // validate the shipping address limit
            $this->validateShippingAddressLimit($shippingAddresses);

            // set the owner
            $data->owner = $this->microMapper->map($user, UserApi::class, [
                MicroMapperInterface::MAX_DEPTH => 0,
            ]);
        }

        return $this->processor->process($data, $operation, $uriVariables, $context);
    }

    private function validateShippingAddressLimit(array $shippingAddresses): void
    {
        if (count($shippingAddresses) >= 3) {
            throw new MaxShippingAddressReachedException();
        }
    }

}
