<?php

declare(strict_types = 1);

namespace App\Contracts;

interface CloudServiceProviderInterface
{
    /**
     * Retrieves the file stored in the bucket.
     *
     * @param string $key Key is the file name/identifier in bucket
     * @return string Returns the URL that points to the file in the bucket
     */
    public function getBucketUrl(string $key, bool $isPreSigned = false): string;

    public function deleteObject(string $key): void;
}