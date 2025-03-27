<?php

declare(strict_types = 1);

namespace App\Security\Voter;

use App\ApiResource\Product\ProductApi;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use Symfony\Component\Security\Core\User\UserInterface;

final class ProductVoter extends Voter
{
    public const PATCH  = 'PATCH';
    public const DELETE = 'DELETE';

    public function __construct (
        private readonly Security $security,
    )
    {
    }

    protected function supports(string $attribute, mixed $subject): bool
    {
        return in_array($attribute, [self::DELETE, self::PATCH])
            && $subject instanceof ProductApi;
    }

    protected function voteOnAttribute(string $attribute, mixed $subject, TokenInterface $token): bool
    {
        $user = $token->getUser();

        // if the user is anonymous, do not grant access
        if (!$user instanceof UserInterface) {
            return false;
        }

        if ( $this->security->isGranted('ROLE_ADMIN')) {
            return true;
        }

        assert($subject instanceof ProductApi);

        switch ($attribute) {
            case self::DELETE:
                // Allow TO DELETE if user has "ROLE_PRODUCT_DELETE" role
                if ( $this->security->isGranted('ROLE_PRODUCT_DELETE') ) {
                    return true;
                }

                return false;
            case self::PATCH:

                // Allow TO EDIT for user having this "ROLE_PRODUCT_EDIT" role
                if ( $this->security->isGranted('ROLE_PRODUCT_EDIT') ) {
                    return true;
                }

                return false;
        }

        return false;
    }
}
