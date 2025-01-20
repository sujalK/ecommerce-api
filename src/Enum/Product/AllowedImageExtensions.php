<?php

declare(strict_types = 1);

namespace App\Enum\Product;

enum AllowedImageExtensions: string
{
    case JPG  = 'jpg';
    case JPEG = 'jpeg';
    case PNG  = 'png';
    case GIF  = 'gif';

    public static function toArray(): array
    {
        return array_map(fn (self $case) => $case->value, self::cases());
    }
}