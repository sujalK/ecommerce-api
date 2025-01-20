<?php

declare(strict_types = 1);

namespace App\DataObjects;

use Symfony\Component\HttpFoundation\File\UploadedFile;

class ProductRequestData
{
    public function __construct (
        public readonly string $productId,
        public readonly UploadedFile $uploadedFile,
    )
    {
    }
}