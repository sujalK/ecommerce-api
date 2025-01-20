<?php

declare(strict_types = 1);

namespace App\Service;

use App\Contracts\File\FileProcessorInterface;
use App\Contracts\File\S3\FileUploaderInterface;
use App\DataObjects\FileUploadData;
use App\Exception\FileNotFoundException;
use App\Exception\InvalidFileException;
use Symfony\Component\HttpFoundation\Request;

class FileUploaderService implements FileProcessorInterface
{

    public function __construct (
        private readonly FileValidationService    $validationService,
        private readonly FileInfoExtractorService $fileInfoExtractor,
        private readonly FileUploaderInterface    $fileUploader,
        private readonly UploadFileSizeProvider   $fileSizeProvider,
    ) {
    }

    /**
     * @throws InvalidFileException
     * @throws FileNotFoundException
     */
    public function initUpload(Request $request): FileUploadData
    {
        $uploadedFile = $request->files->get('file');

        return $this->process($uploadedFile);
    }

    /**
     * @throws InvalidFileException
     * @throws FileNotFoundException
     */
    public function process($file = null): FileUploadData
    {
        try {
            $file = $this->validationService->validateFile($file, $this->fileSizeProvider->getFileSize());

            $filePath         = $this->fileInfoExtractor->getFilePath($file);
            $realMimeType     = $this->fileInfoExtractor->getMimeType($filePath);
            $originalFileName = $this->fileInfoExtractor->getOriginalName($file);
        } catch (FileNotFoundException|InvalidFileException $e) {
            throw $e;
        }

        return $this->fileUploader->upload($originalFileName, $filePath, $realMimeType);
    }

}