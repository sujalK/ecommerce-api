<?php

declare(strict_types = 1);

namespace App\Exception;

use Exception;
use Throwable;

class InvalidProductInfoException extends Exception
{
    public array $errors = [];

    public function __construct(array $errors, string $message = "", int $code = 0, ?Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
        $this->errors = $errors;
    }

    public function getErrors(): array
    {
        return $this->errors;
    }
}