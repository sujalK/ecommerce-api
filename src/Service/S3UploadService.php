<?php

declare(strict_types = 1);

namespace App\Service;

use App\Contracts\File\S3\FileUploaderInterface;
use App\Contracts\S3FileUploaderInterface;
use App\DataObjects\FileUploadData;
use Aws\S3\S3Client;

class S3UploadService implements FileUploaderInterface, S3FileUploaderInterface
{

    public CONST FILE_PREFIX = 'product_image_';

    public function __construct (
        private readonly S3Client $s3Client,
        private readonly string   $s3BucketName,
    )
    {
    }

    public function upload(string $originalFileName, string $filePath, string $realMimeType): FileUploadData
    {
        $s3FileName = $this->getS3FileName($originalFileName);

        $this->uploadToS3($s3FileName, $filePath, $realMimeType);

        return new FileUploadData($s3FileName, $originalFileName);
    }

    public function uploadToS3(string $s3FileName, bool|string $filePath, string $realMimeType): void
    {
        $this->s3Client->putObject([
            'Bucket'      => $this->s3BucketName,
            'Key'         => $s3FileName,
            'SourceFile'  => $filePath,
            'ContentType' => $realMimeType,
        ]);
    }

    private function getS3FileName(string $originalFileName, string $filePrefix = self::FILE_PREFIX): string
    {
        return $filePrefix . bin2hex(random_bytes(16)) /* . '-' . $originalFileName */;
    }
}