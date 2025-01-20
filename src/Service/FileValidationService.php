<?php

declare(strict_types = 1);

namespace App\Service;

use App\Enum\Product\AllowedImageExtensions;
use App\Enum\Product\AllowedMimeTypes;
use App\Exception\FileNotFoundException;
use App\Exception\FileTooLargeException;
use App\Exception\InvalidFileException;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class FileValidationService
{

    public function __construct (
        private readonly FileInfoExtractorService $fileInfoExtractor,
        private readonly UploadFileSizeProvider $fileSizeProvider,
    )
    {
    }

    /**
     * @throws FileTooLargeException
     * @throws FileNotFoundException
     * @throws InvalidFileException
     */
    public function validateFile(UploadedFile $file, ?int $maxFileSizeInBytes = null): UploadedFile
    {
        $this->checkFileExistence($file);
        $this->checkFileValidity($file);
        $this->validateMimeType($file);
        $this->validateFileExtension($file);
        $this->validateFileSize ($file, $maxFileSizeInBytes ?? $this->fileSizeProvider->getFileSize());

        return $file;
    }

    public function validateMimeType(UploadedFile $file): void
    {
        $realMimeType = $this->fileInfoExtractor->getRealMimeType($file);

        if (!in_array($realMimeType, AllowedMimeTypes::toArray(), true)) {
            throw new InvalidFileException();
        }
    }

    public function validateFileExtension(UploadedFile $file): void
    {
        $fileExtension = strtolower($this->fileInfoExtractor->getFileExtension($file));

        if (!in_array($fileExtension, AllowedImageExtensions::toArray(), true)) {
            throw new InvalidFileException();
        }
    }

    public function checkFileExistence(?UploadedFile $file = null): void
    {
        if (!$file) {
            throw new FileNotFoundException();
        }
    }

    public function checkFileValidity(UploadedFile $file): void
    {
        if (!$file->isValid()) {
            throw new InvalidFileException();
        }
    }

    public function validateFileSize(UploadedFile $file, int $maxFileSizeInBytes): void
    {
        $fileSize = $this->fileInfoExtractor->getFileSize($file);
        if ($fileSize === false || $fileSize > $maxFileSizeInBytes) {
            throw new FileTooLargeException();
        }
    }
}