<?php

declare(strict_types = 1);

namespace App\Security\Voter;

use App\ApiResource\Payment\PaymentApi;
use App\Entity\Order;
use App\Entity\Payment;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfonycasts\MicroMapper\MicroMapperInterface;

final class PaymentVoter extends Voter
{
    public const string VIEW   = 'VIEW';
    public const string CREATE = 'CREATE';

    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly MicroMapperInterface $microMapper,
        private readonly Security $security,
    )
    {
    }

    protected function supports(string $attribute, mixed $subject): bool
    {
        return in_array($attribute, [self::VIEW, self::CREATE])
            && $subject instanceof PaymentApi;
    }

    protected function voteOnAttribute(string $attribute, mixed $subject, TokenInterface $token): bool
    {
        $user = $token->getUser();

        // if the user is anonymous, do not grant access
        if (!$user instanceof UserInterface) {
            return false;
        }

        assert($user instanceof User);
        assert($subject instanceof PaymentApi);

        // Grant access for admin
        if ($this->security->isGranted('ROLE_ADMIN')) {
            return true;
        }

        // ... (check conditions and return true to grant permission) ...
        switch ($attribute) {
            case self::VIEW:

                // convert to the Payment instance
                $payment = $this->microMapper->map($subject, Payment::class, [
                    MicroMapperInterface::MAX_DEPTH => 0,
                ]);

                // Fetch the related order
                $order = $this->entityManager->getRepository(Order::class)->find($payment->getOrder()->getId());

                if (!$order) {
                    return false;
                }

                if ($order->getOwnedBy()->getId() === $user->getId()) {
                    return true;
                }

                return false;

            case self::CREATE:

                if ($subject->order->ownedBy->id === $user->getId()) {
                    return true;
                }

                return false;

        }

        return false;
    }
}
