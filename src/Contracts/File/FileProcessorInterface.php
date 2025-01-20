<?php

declare(strict_types = 1);

namespace App\Contracts\File;

interface FileProcessorInterface
{
    public function process($file = null);
}