<?php

declare(strict_types = 1);

namespace App\Contracts\File\S3;

use App\DataObjects\FileUploadData;

interface FileUploaderInterface
{
    /**
     * This method uploads a file to S3 and returns the file name (unique name)
     *
     * @param string $originalFileName
     * @param string $filePath
     * @param string $realMimeType
     * @return FileUploadData
     */
    public function upload(string $originalFileName, string $filePath, string $realMimeType): FileUploadData;
}