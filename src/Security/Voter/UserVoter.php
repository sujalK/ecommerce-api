<?php

declare(strict_types = 1);

namespace App\Security\Voter;

use App\ApiResource\User\UserApi;
use App\Entity\User;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use Symfony\Component\Security\Core\User\UserInterface;

final class UserVoter extends Voter
{

    public const string EDIT   = 'EDIT';
    public const string DELETE = 'DELETE';
    public const string VIEW   = 'VIEW';

    public function __construct (
        private readonly Security $security,
    )
    {
    }

    protected function supports(string $attribute, mixed $subject): bool
    {
        return in_array($attribute, [self::VIEW, self::EDIT, self::DELETE])
            && $subject instanceof UserApi;
    }

    protected function voteOnAttribute(string $attribute, mixed $subject, TokenInterface $token): bool
    {
        $user = $token->getUser();

        // if the user is anonymous, do not grant access
        if (!$user instanceof UserInterface) {
            return false;
        }

        if ($this->security->isGranted('ROLE_ADMIN')) {
            return true;
        }

        $user = $this->security->getUser();
        assert($user instanceof User);
        assert($subject instanceof UserApi);

        // ... (check conditions and return true to grant permission) ...
        switch ($attribute) {
            case self::VIEW:
            case self::DELETE:
            case self::EDIT:

                if ($subject->id !== $user->getId()) {
                    return false;
                }

                return true;
        }

        return false;
    }
}
