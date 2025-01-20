<?php

declare(strict_types = 1);

namespace App\DataObjects;

enum PreSignedUrlExpiryTimeData: string
{
    case TEN_MINUTES            = '+10 minutes';
    case TWENTY_MINUTES         = '+20 minutes';
    case THIRTY_MINUTES         = '+30 minutes';
    case FORTY_MINUTES          = '+40 minutes';
    case FIFTY_MINUTES          = '+50 minutes';
    case SIXTY_MINUTES          = '+60 minutes';
    case SEVENTY_MINUTES        = '+70 minutes';
    case EIGHTY_MINUTES         = '+80 minutes';
    case NINETY_MINUTES         = '+90 minutes';
    case HUNDRED_MINUTES        = '+100 minutes';
    case HUNDRED_TEN_MINUTES    = '+110 minutes';
    case HUNDRED_TWENTY_MINUTES = '+120 minutes';

    public static function toString(self $data): string
    {
        return $data->value;
    }
}