<?php

declare(strict_types = 1);

namespace App\Enum;

enum EnvVars: string
{
    case BUCKET_NAME = 'aws.bucket_name';
    case REGION      = 'aws.region';
}