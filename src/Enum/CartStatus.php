<?php

declare(strict_types = 1);

namespace App\Enum;

enum CartStatus: string
{
    case ACTIVE      = 'active';
    case ABANDONED   = 'abandoned';

    public function getDescription(): string
    {
        return match($this) {
            self::ACTIVE    => 'cart is still active',
            self::ABANDONED => 'user has moved away from the cart',
        };
    }
}