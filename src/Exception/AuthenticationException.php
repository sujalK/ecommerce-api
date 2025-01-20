<?php

namespace App\Exception;

use Exception;
use JetBrains\PhpStorm\Internal\LanguageLevelTypeAware;

class AuthenticationException extends Exception
{
    public function __construct(string $message = "", int $code = 0, ?Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}