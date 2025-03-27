<?php

declare(strict_types = 1);

namespace App\Validator;

use Symfony\Component\Validator\Constraint;

#[\Attribute(\Attribute::TARGET_CLASS | \Attribute::IS_REPEATABLE)]
class CannotChangeProduct extends Constraint
{
    public string $message = 'The product cannot be changed/modified during PATCH operation.';

    public function getTargets(): string|array
    {
        return parent::CLASS_CONSTRAINT;
    }
}
