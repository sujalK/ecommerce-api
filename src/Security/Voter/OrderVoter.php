<?php

namespace App\Security\Voter;

use App\ApiResource\Order\OrderApi;
use App\Entity\User;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use Symfony\Component\Security\Core\User\UserInterface;

final class OrderVoter extends Voter
{
    public const POST = 'POST';

    public function __construct (
        private readonly Security $security,
    )
    {
    }

    protected function supports(string $attribute, mixed $subject): bool
    {
        return in_array($attribute, [self::POST])
            && $subject instanceof OrderApi;
    }

    protected function voteOnAttribute(string $attribute, mixed $subject, TokenInterface $token): bool
    {
        $user = $token->getUser();

        // if the user is anonymous, do not grant access
        if (!$user instanceof UserInterface) {
            return false;
        }

        assert($user instanceof User);
        assert($subject instanceof OrderApi);

        // Allow admin user to make order
        if ( $this->security->isGranted('ROLE_ADMIN') ) {
            return true;
        }

        // ... (check conditions and return true to grant permission) ...
        switch ($attribute) {
            case self::POST:

                if ($subject->ownedBy->id === $user->getId()) {
                    return true;
                }

                break;
        }

        return false;
    }
}
