<?php

declare(strict_types = 1);

namespace App\State;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use App\Contracts\ErrorHandlerInterface;
use App\Contracts\HttpResponseInterface;
use App\Entity\Coupon;
use App\Entity\Order;
use App\Entity\User;
use App\Exception\CouponExpiredException;
use App\Exception\CouponGlobalUsageLimitExceededException;
use App\Exception\CouponNotFoundException;
use App\Exception\CouponSingleUserLimitExceededException;
use App\Exception\InvalidCouponException;
use App\Exception\PendingOrderNotFoundException;
use App\Repository\CouponRepository;
use App\Repository\OrderRepository;
use App\Service\CouponErrorHandlerService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;
use Throwable;

class ApplyCouponToPendingOrderStateProcessor implements ProcessorInterface
{

    public function __construct (
        private readonly Security $security,
        private readonly RequestStack $requestStack,
        private readonly OrderRepository $orderRepository,
        private readonly CouponRepository $couponRepository,
        private readonly EntityManagerInterface $entityManager,
        private readonly HttpResponseInterface $httpResponse,
        #[Autowire(service: CouponErrorHandlerService::class)] private readonly ErrorHandlerInterface $errorHandler,
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

        $response = $this->checkValidity($data->couponCode ?? '');
        if (! $response instanceof Coupon) {
            $this->removeCoupon($existingOrder);
            return $response;
        }

        $coupon = $response;

        try {
            // check coupon usage
            $this->checkCouponUsage($data->couponCode ?? '', $coupon, $user);
        } catch ( CouponSingleUserLimitExceededException ) {
            $this->removeCoupon($existingOrder);
            return $this->httpResponse->validationErrorResponse(errors: ['You have already used this coupon the maximum allowed times.']);
        } catch ( CouponGlobalUsageLimitExceededException ) {
            $this->removeCoupon($existingOrder);
            return $this->httpResponse->validationErrorResponse(errors: ['Coupon has reached its maximum usage limit.']);
        }

        // if order is found, then set the id
        $data->id = $orderId;

        // set the coupon
        $existingOrder->setCouponCode($data->couponCode);
        $this->entityManager->persist($existingOrder);
        $this->entityManager->flush();

        return new JsonResponse([
            'discountType'  => $coupon->getDiscountType(),
            'discountValue' => $coupon->getDiscountValue(),
            'appliesTo'     => $coupon->getAppliesTo(),
        ], 200);
    }

    public function checkValidity(string $couponCode): Response|Coupon
    {
        try {
            $coupon = $this->getCoupon($couponCode);

            $this->checkIfCouponIsUsedBeforeStartDate($coupon);
            $this->checkExpiry($coupon);

            return $coupon;
        } catch (Throwable $e) {
            return $this->errorHandler->handleError($e);
        }
    }

    private function getCoupon(string $couponCode): Coupon
    {
        $coupon = $this->couponRepository->findOneBy(['code' => $couponCode]);

        if (! $coupon) {
            throw new CouponNotFoundException(['Invalid coupon.']);
        }

        return $coupon;
    }

    private function checkIfCouponIsUsedBeforeStartDate(Coupon $coupon): void
    {
        $now = new \DateTime();
        if ($now < $coupon->getStartDate()) {
            throw new InvalidCouponException(['The coupon is not applicable.']);
        }
    }

    private function checkExpiry(Coupon $coupon): void
    {
        $now = new \DateTime();
        if ( (!$coupon->isActive()) || ($now > $coupon->getEndDate()) ) {
            throw new CouponExpiredException(['The coupon is expired.']);
        }
    }

    private function checkCouponUsage(string $couponCode, Coupon $coupon, User $user): void
    {
        /**
         * check in the orders, if the user has already used
         * the same coupon max amount of times i.e. exceed usage limit.
         */
        $globalCouponUsageCount = $this->orderRepository->findUsedCouponsCountGlobally($couponCode);
        if ($globalCouponUsageCount >= $coupon->getUsageLimit()) {
            throw new CouponGlobalUsageLimitExceededException();
        }

        // Find all the orders
        $couponUsageCount = $this->orderRepository->findUsedCouponsCount($couponCode, $user);
        if ($couponUsageCount >= $coupon->getSingleUserLimit()) {
            throw new CouponSingleUserLimitExceededException();
        }
    }

    private function removeCoupon(Order $existingOrder): void
    {
        $existingOrder->setCouponCode(NULL);

        $this->entityManager->flush();
    }
}
