<?php

namespace App\State;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use App\Contracts\HttpResponseInterface;
use App\Entity\Cart;
use App\Entity\User;
use App\Enum\ActivityLog;
use App\Repository\CartRepository;
use App\Service\ActivityLogService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\SecurityBundle\Security;

class RemoveCouponStateProcessor implements ProcessorInterface
{

    public function __construct (
        private readonly CartRepository $cartRepository,
        private readonly HttpResponseInterface $httpResponse,
        private readonly EntityManagerInterface $entityManager,
        private readonly ActivityLogService $activityLogService,
        private readonly Security $security,
    )
    {
    }

    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = [])
    {

        $user = $this->security->getUser();
        assert($user instanceof User);

        $cart = $this->findActiveCart($user);

        if ( ! $cart ) {
            return $this->httpResponse->invalidDataResponse(errors: ['No cart found to apply the coupon.']);
        }

        // check if active cart belongs to the logged-in user
        if ($cart->getOwner() !== $user) {
            return $this->httpResponse->forbiddenResponse();
        }

        $cart->setCouponCode(null);
        $cart->setUpdatedAt(new \DateTimeImmutable('now', new \DateTimeZone('UTC')));

        // log the activity
        $this->log($cart);

        $this->entityManager->flush();

        return [
            'message' => 'Coupon code removed successfully.'
        ];
    }

    public function findActiveCart(User $user): ?Cart
    {
        /* we need to store the coupon on the cart, so we need to query the cart table first */
        return $this->cartRepository->findOneBy(
            ['owner'     => $user, 'status' => 'active'],
            ['createdAt' => 'DESC']
        );
    }

    public function log(Cart $cart): void
    {
        $couponCode = new \stdClass();
        $couponCode->couponCode = $cart->getCouponCode();

        $this->activityLogService->storeLog(ActivityLog::REMOVE_COUPON, $couponCode);
    }
}
