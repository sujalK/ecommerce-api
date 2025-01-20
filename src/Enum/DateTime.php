<?php

declare(strict_types = 1);

namespace App\Enum;

enum DateTime: string
{
    case CURRENT   = 'now';
    case YESTERDAY = 'yesterday';
    case TODAY     = 'today';
    case TOMORROW  = 'tomorrow';
    case MIDNIGHT  = 'midnight';
}