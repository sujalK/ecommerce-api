<?php

declare(strict_types = 1);

namespace App\Enum;

enum HttpStatus: int
{
    case OK                    = 200;
    case CREATED               = 201;
    case NO_CONTENT            = 204;
    case BAD_REQUEST           = 400;
    case UNAUTHORIZED          = 401;
    case UNPROCESSABLE_ENTITY  = 422;
    case FORBIDDEN             = 403;
    case NOT_FOUND             = 404;
    case INTERNAL_SERVER_ERROR = 500;

    /**
     * Returns the textual description of the Http Status
     * @return string
     */
    public function toString(): string
    {
        return match( $this ) {
            self::OK                    => 'OK',
            self::CREATED               => 'Created',
            self::NO_CONTENT            => 'No Content',
            self::BAD_REQUEST           => 'Bad Request',
            self::UNAUTHORIZED          => 'Unauthorized',
            self::UNPROCESSABLE_ENTITY  => 'Unprocessable Entity',
            self::FORBIDDEN             => 'Forbidden',
            self::NOT_FOUND             => 'Not Found',
            self::INTERNAL_SERVER_ERROR => 'Internal Server Error',
        };
    }
}