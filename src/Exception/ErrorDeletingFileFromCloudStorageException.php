<?php

declare(strict_types = 1);

namespace App\Exception;

use Exception;
use Throwable;

class ErrorDeletingFileFromCloudStorageException extends Exception
{
    public function __construct (
        private readonly ?string $description = null,
        public readonly array $errors         = [],
        string $message                       = "",
        int $code                             = 0,
        ?Throwable $previous                  = null,
    )
    {
        parent::__construct($message, $code, $previous);
    }

    public function getErrors(): array
    {
        return $this->errors;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }
}