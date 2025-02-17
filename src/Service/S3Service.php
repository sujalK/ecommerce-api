<?php

declare(strict_types = 1);

namespace App\Service;

use App\Contracts\CloudServiceProviderInterface;
use App\Contracts\EnvironmentVariablesServiceInterface;
use App\Enum\EnvVars;
use App\Enum\PreSignedUrlExpiryTimeData;
use App\Exception\ErrorDeletingFileFromCloudStorageException;
use App\Exception\MissingObjectKeyException;
use Aws\Exception\AwsException;
use Aws\S3\S3Client;

class S3Service implements CloudServiceProviderInterface
{
    private readonly string $bucketName;
    private readonly string $region;

    public function __construct (
        private readonly EnvironmentVariablesServiceInterface $envVar,
        private readonly S3Client $s3Client,
    )
    {
        $this->bucketName = $this->envVar->get(EnvVars::BUCKET_NAME);
        $this->region     = $this->envVar->get(EnvVars::REGION);
    }

    public function getBucketUrl(string $key, bool $isPreSigned = false): string
    {
        return $isPreSigned
            ? $this->getPreSignedUrl($key)
            : $this->urlString($this->bucketName, $this->region, $key);
    }

    public function getPreSignedUrl(string $key): string
    {
        // Generate a pre-signed URL dynamically
        $cmd = $this->s3Client->getCommand('GetObject', [
            'Bucket' => $this->bucketName,
            'Key'    => $key,
        ]);

        $request = $this->s3Client->createPresignedRequest($cmd, PreSignedUrlExpiryTimeData::TWENTY_MINUTES->value);

        return (string) $request->getUri();
    }

    /**
     * @throws ErrorDeletingFileFromCloudStorageException
     */
    public function deleteObject(string $key): void
    {
        try {
            $this->s3Client->deleteObject([
                'Bucket' => $this->bucketName,
                'Key'    => $key,
            ]);
        } catch (AwsException $e) {
            throw new ErrorDeletingFileFromCloudStorageException(description: $e->getAwsErrorMessage());
        }
    }

    private function urlString(string $bucketName, string $region, string $key): string
    {
        return sprintf (
            "https://%s.s3.%s.amazonaws.com/%s",
            $bucketName,
            $region,
            $key
        );
    }

}