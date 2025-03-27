<?php

declare(strict_types = 1);

namespace App\Validator;

use App\Repository\UserRepository;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

class IsUniqueUserNameValidator extends ConstraintValidator
{

    public function __construct (
        private UserRepository $userRepository,
    )
    {
    }

    public function validate(mixed $value, Constraint $constraint): void
    {
        /* @var IsUniqueUserName $constraint */

        if (null === $value || '' === $value) {
            return;
        }

        $user = $this->userRepository->findOneBy(['username' => $value]);

        if ($user) {
            $this->context->buildViolation($constraint->message)
                ->addViolation();
        }
    }
}
