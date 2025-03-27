<?php

declare(strict_types = 1);

namespace App\Security\Voter;

use App\ApiResource\Notification\NotificationApi;
use App\Entity\User;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use Symfony\Component\Security\Core\User\UserInterface;

final class NotificationVoter extends Voter
{

    public const string VIEW = 'VIEW';
    public const string EDIT = 'EDIT';

    public function __construct (
        private readonly Security $security,
    )
    {
    }

    protected function supports(string $attribute, mixed $subject): bool
    {
        return in_array($attribute, [self::VIEW, self::EDIT])
            && $subject instanceof NotificationApi;
    }

    protected function voteOnAttribute(string $attribute, mixed $subject, TokenInterface $token): bool
    {
        $user = $token->getUser();

        // if the user is anonymous, do not grant access
        if (!$user instanceof UserInterface) {
            return false;
        }
        assert($user instanceof User);
        assert($subject instanceof NotificationApi);

        if ($this->security->isGranted('ROLE_ADMIN')) {
            return true;
        }

        switch ($attribute) {
            case self::EDIT:
            case self::VIEW:

                if ($subject->ownedBy->id === $user->getId()) {
                    return true;
                }

                return false;
        }

        return false;
    }
}
