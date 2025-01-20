<?php

declare(strict_types = 1);

namespace App\Service;

use Symfony\Component\HttpFoundation\File\UploadedFile;

class FileInfoExtractorService
{
    private readonly \finfo $fInfo;

    public function __construct()
    {
        $this->fInfo = new \finfo(FILEINFO_MIME_TYPE);
    }

    public function getRealMimeType(UploadedFile $file): string|false
    {
        return $this->getMimeType($this->getFilePath($file));
    }

    public function getMimeType(string $filePath): false|string
    {
        return $this->fInfo->file($filePath);
    }

    public function getFilePath(UploadedFile $file): false|string
    {
        return $file->getRealPath();
    }

    public function getFileExtension(UploadedFile $file): string
    {
        return $file->getClientOriginalExtension();
    }

    public function getOriginalName(UploadedFile $file): string
    {
        return $file->getClientOriginalName();
    }

    public function getFileSize(UploadedFile $file): false|int
    {
        return $file->getSize();
    }

}