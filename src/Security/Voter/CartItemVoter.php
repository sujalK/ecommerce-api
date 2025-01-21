<?php

declare(strict_types = 1);

namespace App\Security\Voter;

use App\ApiResource\CartItem\CartItemApi;
use App\Entity\User;
use App\Repository\CartRepository;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use Symfony\Component\Security\Core\User\UserInterface;

final class CartItemVoter extends Voter
{

    public const DELETE = 'DELETE';

    public function __construct (
        private readonly CartRepository $repository,
    )
    {
    }

    protected function supports(string $attribute, mixed $subject): bool
    {
        return in_array($attribute, [self::DELETE])
            && $subject instanceof CartItemApi;
    }

    protected function voteOnAttribute(string $attribute, mixed $subject, TokenInterface $token): bool
    {
        $user = $token->getUser();

        // if the user is anonymous, do not grant access
        if (!$user instanceof User) {
            return false;
        }

        assert($subject instanceof CartItemApi);

        // ... (check conditions and return true to grant permission) ...
        switch ($attribute) {
            case self::DELETE:

                // Get the CartId
                $cartId     = $subject->cart->id;

                // Get the owner of the cart
                $cartOwner = $this->repository->find($cartId)->getOwner();

                // Do not grant access for Delete operation if cart owner id different from the logged-in user
                if ( $cartOwner !== $user ) {
                    return false;
                }

                return true;
        }

        return false;
    }
}
