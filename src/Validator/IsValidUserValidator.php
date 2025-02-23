<?php

declare(strict_types = 1);

namespace App\Validator;

use App\ApiResource\User\UserApi;
use App\Entity\User;
use App\Repository\UserRepository;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

class IsValidUserValidator extends ConstraintValidator
{

    public function __construct (
        private readonly UserRepository $userRepository,
    )
    {
    }

    public function validate(mixed $value, Constraint $constraint): void
    {
        /* @var IsValidUser $constraint */

        if (null === $value || '' === $value) {
            return;
        }

        assert($value instanceof UserApi);

        $user = $this->userRepository->findOneBy(['id' => $value->id]);

        if ( ! $user ) {
            $this->context->buildViolation($constraint->message)
                ->addViolation();
        }

    }
}
