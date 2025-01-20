<?php

declare(strict_types = 1);

namespace App\DataObjects;

class FileUploadData
{
    public function __construct (
        public readonly string $s3FileName,
        public readonly string $originalFileName,
    )
    {
    }
}