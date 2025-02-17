<?php

namespace App\Validator;

use App\ApiResource\User\UserApi;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

class IsValidOwnerValidator extends ConstraintValidator
{

    public function __construct (
        private readonly Security $security,
    )
    {
    }

    public function validate(mixed $value, Constraint $constraint): void
    {

        /* @var IsValidOwner $constraint */

        if (null === $value || '' === $value) {
            return;
        }

        assert($value instanceof UserApi);

        $user = $this->security->getUser();

        if ($value->id !== $user->getId()) {
            $this->context->buildViolation($constraint->message)
                ->addViolation();
        }

    }
}
