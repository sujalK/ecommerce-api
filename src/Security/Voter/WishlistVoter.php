<?php

declare(strict_types = 1);

namespace App\Security\Voter;

use App\ApiResource\Wishlist\WishlistApi;
use App\Entity\User;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use Symfony\Component\Security\Core\User\UserInterface;

final class WishlistVoter extends Voter
{
    public const string DELETE = 'DELETE';
    public const string VIEW   = 'VIEW';

    public function __construct (
        private readonly Security $security,
    )
    {
    }

    protected function supports(string $attribute, mixed $subject): bool
    {
        return in_array($attribute, [self::DELETE, self::VIEW])
            && $subject instanceof WishlistApi;
    }

    protected function voteOnAttribute(string $attribute, mixed $subject, TokenInterface $token): bool
    {
        $user = $token->getUser();

        // if the user is anonymous, do not grant access
        if (!$user instanceof UserInterface) {
            return false;
        }
        assert($user instanceof User);
        assert($subject instanceof WishlistApi);

        // allow admin to delete anyone's wishlist
        if ($this->security->isGranted('ROLE_ADMIN')) {
            return true;
        }

        // ... (check conditions and return true to grant permission) ...
        switch ($attribute) {
            case self::VIEW:
            case self::DELETE:

                if ( $subject->ownedBy->id === $user->getId() ) {
                    return true;
                }

                break;
        }

        return false;
    }
}
