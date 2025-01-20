<?php

namespace App\Validator;

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

class ValidPriceValidator extends ConstraintValidator
{
    public function validate(mixed $value, Constraint $constraint): void
    {
        /* @var ValidPrice $constraint */

        if (null === $value || '' === $value) {
            return;
        }

        if (!preg_match('/^\d{1,10}(\.\d{1,2})?$/', $value)) {
            // Build a violation message if the value is invalid
            $this->context->buildViolation($constraint->message)
                 ->setParameter('{{ value }}', $value)
                 ->addViolation();
        }
    }
}
