<?php

declare(strict_types = 1);

namespace App\Validator;

use Symfony\Component\Validator\Constraint;

#[\Attribute(\Attribute::TARGET_CLASS | \Attribute::IS_REPEATABLE)]
class CanPostReview extends Constraint
{

    public string $message         = 'You cannot post a review for this product.';
    public string $duplicateReview = 'The review already exists.';

    public function getTargets(): string|array // adding return type will avoid deprecation notice
    {
        return self::CLASS_CONSTRAINT;
    }
}
