<?php

declare(strict_types = 1);

namespace App\Enum;

enum ActivityLog: string
{
    case LOGGED_IN        = 'LOGIN';
    case LOGGED_OUT       = 'LOGOUT';
    case DELETE_CART_ITEM = 'DELETE';

    public function getDescription(): string
    {
        return match ($this) {
            self::LOGGED_IN        => 'logged in into the system.',
            self::LOGGED_OUT       => 'logged out from the system',
            self::DELETE_CART_ITEM => $this->getDeleteCartItemDescription(),
        };
    }

    public function getDeleteCartItemDescription(?int $id = null): string
    {
        if ($id !== null) {
            return sprintf('Remove cart Item (Product id: %s)', $id);
        }

        return 'Remove cart Item';
    }
}