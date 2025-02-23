<?php

declare(strict_types = 1);

namespace App\Validator;

use Symfony\Component\Validator\Constraint;

#[\Attribute(\Attribute::TARGET_PROPERTY | \Attribute::TARGET_METHOD | \Attribute::IS_REPEATABLE)]
class IsValidUser extends Constraint
{
    public string $message = 'The user does not exist.';
}
