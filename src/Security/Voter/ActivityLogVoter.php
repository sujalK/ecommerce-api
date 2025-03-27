<?php

namespace App\Security\Voter;

use App\ApiResource\ActivityLog\ActivityLogApi;
use App\Entity\User;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

final class ActivityLogVoter extends Voter
{
    public const string VIEW = 'ACTIVITY_LOG_VIEW';

    public function __construct(
        private readonly Security $security,
    )
    {
    }

    protected function supports(string $attribute, mixed $subject): bool
    {
        return in_array($attribute, [self::VIEW])
            && $subject instanceof ActivityLogApi;
    }

    protected function voteOnAttribute(string $attribute, mixed $subject, TokenInterface $token): bool
    {
        $user = $token->getUser();

        // if the user is anonymous, do not grant access
        if (!$user instanceof User) {
            return false;
        }

        assert($subject instanceof ActivityLogApi);

        // grant access to the Admin
        if ( $this->security->isGranted('ROLE_ADMIN') ) {
            return true;
        }

        switch ($attribute) {
            case self::VIEW:

                if ( $subject->owner->id === $user->getId() ) {
                    return true;
                }

                break;
        }

        return false;
    }
}
