<?php

declare(strict_types = 1);

namespace App\Exception;

use Throwable;

class CouponExpiredException extends \Exception
{
    private readonly array $errors;

    public function __construct(array $errors = [], string $message = "", int $code = 0, ?Throwable $previous = null)
    {
        $this->errors = $errors;
        parent::__construct($message, $code, $previous);
    }

    public function getErrors(): array
    {
        return $this->errors;
    }

}