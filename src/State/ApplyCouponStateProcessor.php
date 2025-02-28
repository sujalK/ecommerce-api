<?php

declare(strict_types = 1);

namespace App\State;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use App\ApiResource\Discount\DiscountApi;
use App\Contracts\HttpResponseInterface;
use App\DataObjects\DiscountData;
use App\Entity\Cart;
use App\Entity\Coupon;
use App\Entity\User;
use App\Enum\ActivityLog;
use App\Exception\CouponGlobalUsageLimitExceededException;
use App\Exception\CouponSingleUserLimitExceededException;
use App\Repository\CartRepository;
use App\Repository\OrderRepository;
use App\Service\ActivityLogService;
use App\Service\CouponValidatorService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\JsonResponse;

class ApplyCouponStateProcessor implements ProcessorInterface
{

    public function __construct (
        private readonly Security $security,
        private readonly HttpResponseInterface $httpResponse,
        private readonly CouponValidatorService $couponValidatorService,
        private readonly CartRepository $cartRepository,
        private readonly EntityManagerInterface $entityManager,
        private readonly OrderRepository $orderRepository,
        private readonly ActivityLogService $activityLogService,
    )
    {
    }

    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = [])
    {
        assert($data instanceof DiscountApi);
        
        /** @var string $couponCode */
        $couponCode = $data->couponCode;

        $user = $this->security->getUser();
        assert($user instanceof User);

        /** @var Coupon|JsonResponse $response */
        $response = $this->couponValidatorService->validateCoupon($couponCode);

        // active cart of a user, the cart that is currently active
        $activeCart = $this->findActiveCart($user);

        // If the coupon is not active, then update the coupon to null
        if ( ! $activeCart ) {
            $this->removeCoupon($activeCart);

            return $this->httpResponse->invalidDataResponse(errors: ['No cart found to apply the coupon.']);
        }

        // check if active cart belongs to the logged-in user
        if ($activeCart->getOwner() !== $user) {
            return $this->httpResponse->forbiddenResponse();
        }

        // if $response is not Coupon, then return $response
        if (! $response instanceof Coupon) {
            $this->removeCoupon($activeCart);

            return $response;
        }

        $coupon = $response;
        assert($coupon instanceof Coupon);

        try {
            $this->checkCouponUsage($couponCode, $coupon, $user);
        } catch (CouponSingleUserLimitExceededException) {
            $this->removeCoupon($activeCart);
            return $this->httpResponse->validationErrorResponse(errors: ['You have already used this coupon the maximum allowed times.']);
        } catch (CouponGlobalUsageLimitExceededException) {
            $this->removeCoupon($activeCart);
            return $this->httpResponse->validationErrorResponse(errors: ['Coupon has reached its maximum usage limit.']);
        }

        // add coupon to cart
        $this->applyCouponToCart($activeCart, $couponCode);

        // log the activity
        $this->log($couponCode);

        return $this->getDiscountData($coupon);
    }

    public function findActiveCart(User $user): ?Cart
    {
        /* we need to store the coupon on the cart, so we need to query the cart table first */
        return $this->cartRepository->findOneBy(
            ['owner'     => $user, 'status' => 'active'],
            ['createdAt' => 'DESC']
        );
    }

    public function removeCoupon(?Cart $activeCart): void
    {
        if (!$activeCart) {
            return;
        }

        $activeCart->setCouponCode(null);
        $this->entityManager->flush();
    }

    public function applyCouponToCart(Cart $activeCart, string $couponCode): void
    {
        // set coupon code to the current ( recently created ) active cart of a logged-in user
        $activeCart->setCouponCode($couponCode);
        $activeCart->setUpdatedAt(new \DateTimeImmutable('now', new \DateTimeZone('UTC')));
        $this->entityManager->flush();
    }

    public function getDiscountData(Coupon $coupon): DiscountData
    {
        return new DiscountData (
            discountType: $coupon->getDiscountType(),
            discountValue: $coupon->getDiscountValue(),
            appliesTo: json_encode($coupon->getAppliesTo())
        );
    }

    public function checkCouponUsage(string $couponCode, Coupon $coupon, User $user): void
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

    public function log(string $couponCode): void
    {
        // store the coupon code in object
        $couponData = new \stdClass();
        $couponData->couponCode = $couponCode;

        $this->activityLogService->storeLog(ActivityLog::APPLY_COUPON, $couponData);
    }
}
