<?php

declare(strict_types = 1);

namespace App\Enum\Product;

enum AllowedMimeTypes: string
{
    case JPEG = 'image/jpeg';
    case PNG  = 'image/png';
    case GIF  = 'image/gif';

    public static function toArray(): array
    {
        return array_map(fn(self $case) => $case->value, self::cases());
    }
}