<?php

declare(strict_types = 1);

namespace App\Contracts;

use App\Contracts\File\S3\FileUploaderInterface;

interface S3FileUploaderInterface
{
    public function uploadToS3(string $s3FileName, bool|string $filePath, string $realMimeType): void;
}