<?php

declare(strict_types = 1);

namespace App\Security\Voter;

use App\ApiResource\Cart\CartApi;
use App\Entity\User;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

final class CartVoter extends Voter
{

    public const VIEW = 'VIEW';
    public const POST = 'POST';
    public const EDIT = 'EDIT';

    public function __construct (
        private readonly Security $security,
    )
    {
    }

    protected function supports(string $attribute, mixed $subject): bool
    {
        return in_array($attribute, [self::VIEW, self::POST, self::EDIT,])
            && $subject instanceof CartApi;
    }

    protected function voteOnAttribute(string $attribute, mixed $subject, TokenInterface $token): bool
    {
        assert($subject instanceof CartApi);
        $user = $token->getUser();

        // if the user is anonymous, do not grant access
        if (!$user instanceof User) {
            return false;
        }

        switch ($attribute) {
            case self::VIEW:

                if ( ! $this->security->isGranted('ROLE_CART_READ') ) {
                    return false;
                }

                if ( $user->getId() === $subject->owner->id ) {
                    return true;
                }

                break;
            case self::POST:

                if ( ! $this->security->isGranted('ROLE_CART_ADD') ) {
                    return false;
                }

                if ( $user->getId() === $subject->owner->id ) {
                    return true;
                }

                break;
            case self::EDIT:

                if ( ! $this->security->isGranted('ROLE_CART_EDIT') ) {
                    return false;
                }

                if ( $user->getId() === $subject->owner->id ) {
                    return true;
                }
        }

        return false;
    }
}
