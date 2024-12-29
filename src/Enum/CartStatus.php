<?php

declare(strict_types = 1);

namespace App\Enum;

enum CartStatus: string
{
    case ACTIVE    = 'active';
    case ABANDONED = 'abandoned';
}