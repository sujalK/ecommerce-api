<?php

declare(strict_types = 1);

namespace App\DataObjects\ErrorResponse;

use App\Enum\HttpStatus;

class ResponseData
{
    public function __construct (
        public int $statusCode,
        public string $status,
        public string $message,
        public ?array $errors       = null,
        public ?string $description = '',
    )
    {
    }

    public static function withDefaults(string $status, string $message, HttpStatus $statusCode, array $errors = [], ?string $description = null): self
    {
        return new self (
            statusCode:  $statusCode->value,
            status:      $status,
            message:     $message,
            errors:      $errors,
            description: $description ?? 'n/a',
        );
    }
}