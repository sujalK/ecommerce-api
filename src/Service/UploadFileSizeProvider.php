<?php

declare(strict_types = 1);

namespace App\Service;

use Symfony\Bundle\SecurityBundle\Security;

class UploadFileSizeProvider
{
    /**
     * Role definition for the admin
     */
    private const string ROLE_ADMIN = 'ROLE_ADMIN';

    public function __construct (
        private readonly int $defaultMaxSize,
        private readonly int $adminMaxSize,
        private readonly Security $security,
    )
    {
    }

    public function getAdminMaxUploadSize(): int
    {
        return $this->adminMaxSize;
    }

    public function getUserMaxUploadSize(): int
    {
        return $this->defaultMaxSize;
    }

    public function getFileSize(): int
    {
        return  $this->security->isGranted(self::ROLE_ADMIN)
            ?   $this->getAdminMaxUploadSize()
            :   $this->getUserMaxUploadSize();
    }

}