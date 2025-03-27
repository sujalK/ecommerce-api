<?php

declare(strict_types = 1);

namespace App\Security\Voter;

use App\ApiResource\ProductReview\ProductReviewApi;
use App\Entity\OrderItem;
use App\Entity\ProductReview;
use App\Entity\User;
use App\Repository\OrderItemRepository;
use App\Repository\ProductReviewRepository;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use Symfony\Component\Security\Core\User\UserInterface;

final class ProductReviewVoter extends Voter
{

    public const string EDIT   = 'EDIT_REVIEW';
    public const string DELETE = 'DELETE_REVIEW';

    public function __construct (
        private readonly Security $security,
    )
    {
    }

    protected function supports(string $attribute, mixed $subject): bool
    {
        return in_array($attribute, [self::EDIT, self::DELETE])
            && $subject instanceof ProductReviewApi;
    }

    protected function voteOnAttribute(string $attribute, mixed $subject, TokenInterface $token): bool
    {
        $user = $token->getUser();

        // if the user is anonymous, do not grant access
        if (!$user instanceof UserInterface) {
            return false;
        }
        assert($subject instanceof ProductReviewApi);
        assert($user instanceof User);

        if ($this->security->isGranted('ROLE_ADMIN')) {
            return true;
        }

        // ... (check conditions and return true to grant permission) ...
        switch ($attribute) {
            case self::EDIT:
                if ($subject->owner->id === $user->getId()) {
                    return true;
                }

                return false;
            case self::DELETE:

                if ($subject->owner->id !== $user->getId()) {
                    return false;
                }

                return true;
        }

        return false;
    }

}
