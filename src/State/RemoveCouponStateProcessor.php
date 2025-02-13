<?php

namespace App\State;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use App\Contracts\HttpResponseInterface;
use App\Entity\Cart;
use App\Entity\User;
use App\Repository\CartRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\SecurityBundle\Security;

class RemoveCouponStateProcessor implements ProcessorInterface
{

    public function __construct (
        private readonly CartRepository $cartRepository,
        private readonly HttpResponseInterface $httpResponse,
        private readonly EntityManagerInterface $entityManager,
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

        $cart->setCouponCode(null);
        $cart->setUpdatedAt(new \DateTimeImmutable('now', new \DateTimeZone('UTC')));

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
}
