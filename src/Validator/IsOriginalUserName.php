<?php

declare(strict_types = 1);

namespace App\Validator;

use Symfony\Component\Validator\Constraint;

#[\Attribute(\Attribute::TARGET_CLASS | \Attribute::IS_REPEATABLE)]
class IsOriginalUserName extends Constraint
{

    public string $message = 'The userName cannot be modified/changed.';

    public function getTargets(): string|array
    {
        return parent::CLASS_CONSTRAINT;
    }
}
