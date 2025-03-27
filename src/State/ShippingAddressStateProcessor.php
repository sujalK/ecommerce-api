<?php

declare(strict_types = 1);

namespace App\State;

use ApiPlatform\Metadata\Delete;
use ApiPlatform\Metadata\Operation;
use ApiPlatform\Metadata\Patch;
use ApiPlatform\Metadata\Post;
use ApiPlatform\State\ProcessorInterface;
use App\ApiResource\User\UserApi;
use App\Entity\User;
use App\Enum\ActivityLog;
use App\Exception\MaxShippingAddressReachedException;
use App\Repository\ShippingAddressRepository;
use App\Service\ActivityLogService;
use Symfony\Bundle\SecurityBundle\Security;
use Symfonycasts\MicroMapper\MicroMapperInterface;

class ShippingAddressStateProcessor implements ProcessorInterface
{

    public function __construct(
        private readonly ShippingAddressRepository $shippingAddressRepository,
        private readonly ActivityLogService $activityLogService,
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

        if ($operation instanceof Post) {
            // get the shipping address by the owner
            $shippingAddresses = $this->shippingAddressRepository->findBy(['owner' => $user]);

            // validate the shipping address limit
            $this->validateShippingAddressLimit($shippingAddresses);

            // set the owner
            $data->owner = $this->microMapper->map($user, UserApi::class, [
                MicroMapperInterface::MAX_DEPTH => 0,
            ]);
        }

        if ($operation instanceof Patch) {
            $data->updatedAt = new \DateTimeImmutable('now', new \DateTimeZone('UTC'));
        }

        $entity = $this->processor->process($data, $operation, $uriVariables, $context);

        // log
        $this->log($operation, $entity);

        return $entity;
    }

    private function validateShippingAddressLimit(array $shippingAddresses): void
    {
        if (count($shippingAddresses) >= 3) {
            throw new MaxShippingAddressReachedException();
        }
    }

    public function log(mixed $operation, mixed $entity): void
    {
        if ($operation instanceof Post) {
            $this->activityLogService->storeLog(ActivityLog::CREATE_SHIPPING_ADDRESS, $entity);
        } else if ($operation instanceof Patch) {
            $this->activityLogService->storeLog(ActivityLog::UPDATE_SHIPPING_ADDRESS, $entity);
        } else if ($operation instanceof Delete) {
            $this->activityLogService->storeLog(ActivityLog::DELETE_SHIPPING_ADDRESS);
        }
    }

}
