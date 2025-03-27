<?php

declare(strict_types = 1);

namespace App\State;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use App\Contracts\HttpResponseInterface;
use App\Entity\User;
use App\Exception\PendingOrderNotFoundException;
use App\Repository\OrderRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RequestStack;

class RemoveCouponFromPendingOrder implements ProcessorInterface
{

    public function __construct (
        private readonly EntityManagerInterface $entityManager,
        private readonly OrderRepository $orderRepository,
        private readonly HttpResponseInterface $httpResponse,
        private readonly RequestStack $requestStack,
        private readonly Security $security,
    )
    {
    }

    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = [])
    {
        /*
         * Get the order id
         */
        $orderId = (int) $this->requestStack->getCurrentRequest()->get('orderId');

        if ($orderId === 0) {
            return $this->httpResponse->validationErrorResponse([
                'Invalid orderId, Please make sure to process correct order id.',
            ]);
        }

        /*
         * Get current logged-in user
         */
        $user = $this->security->getUser();
        assert($user instanceof User);


        $existingOrder = $this->orderRepository->findOneBy(['id' => $orderId, 'status' => 'pending', 'ownedBy' => $user]);

        if ( ! $existingOrder ) {
            throw new PendingOrderNotFoundException();
        }

        $couponCodeString = '';
        if ($existingOrder->getCouponCode() === null) {
            $couponCodeString = $existingOrder->getCouponCode();
            return $this->httpResponse->validationErrorResponse([
                'No coupon code is present.'
            ]);
        }

        $tempCouponCode = $existingOrder->getCouponCode();
        $existingOrder->setCouponCode(null);

        $this->entityManager->persist($existingOrder);
        $this->entityManager->flush();

        return new JsonResponse([
            'success'     => true,
            'description' => 'Coupon code '. $tempCouponCode .' has been removed.',
        ], 200);
    }
}
