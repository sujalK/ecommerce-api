<?php

declare(strict_types = 1);

namespace App\Service;

use App\Contracts\ErrorHandlerInterface;
use App\Entity\Coupon;
use App\Exception\CouponExpiredException;
use App\Exception\CouponNotFoundException;
use App\Exception\InvalidCouponException;
use App\Repository\CouponRepository;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\HttpFoundation\Response;
use Throwable;

class CouponValidatorService
{

    public function __construct (
        private readonly CouponRepository $couponRepository,
        #[Autowire(service: CouponErrorHandlerService::class)] private readonly ErrorHandlerInterface $errorHandler,
    )
    {
    }

    public function validateCoupon(string $couponCode): Response|Coupon
    {
        return $this->checkValidity($couponCode);
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
}