<?php

declare(strict_types = 1);

namespace App\Security\Voter;

use App\ApiResource\ShippingAddress\ShippingAddressApi;
use App\Entity\User;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use Symfony\Component\Security\Core\User\UserInterface;

final class ShippingAddressVoter extends Voter
{
    public const string DELETE = 'DELETE';
    public const string EDIT   = 'EDIT';

    public function __construct (
        private readonly Security $security,
    )
    {
    }

    protected function supports(string $attribute, mixed $subject): bool
    {
        return in_array($attribute, [self::EDIT, self::DELETE])
            && $subject instanceof ShippingAddressApi;
    }

    protected function voteOnAttribute(string $attribute, mixed $subject, TokenInterface $token): bool
    {
        $user = $token->getUser();

        // if the user is anonymous, do not grant access
        if (!$user instanceof UserInterface) {
            return false;
        }

        // Make sure admin can grant access
        if ($this->security->isGranted('ROLE_ADMIN')) {
            return true;
        }

        assert($user instanceof User);
        assert($subject instanceof ShippingAddressApi);

        switch ($attribute) {
            case self::DELETE:
            case self::EDIT:
                if ($subject->owner->id === $user->getId()) {
                    return true;
                }

                return false;
        }

        return false;
    }
}
