<?php

declare(strict_types = 1);

namespace App\Validator;

use Symfony\Component\Validator\Constraint;

#[\Attribute(\Attribute::TARGET_CLASS | \Attribute::IS_REPEATABLE)]
class NotAllowedProductChangeInInventory extends Constraint
{
    /*
     * Any public properties become valid options for the annotation.
     * Then, use these in your validator class.
     */
    public string $message = 'You are not allowed to change/modify existing product in the inventory.';

    public function getTargets(): array|string
    {
        return self::CLASS_CONSTRAINT;
    }
}
