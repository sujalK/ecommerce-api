<?php

declare(strict_types = 1);

namespace App\Contracts\Json;

use App\Enum\HttpStatus;
use Symfony\Component\HttpFoundation\JsonResponse;

interface JsonResponderInterface
{
    public function createResponse(HttpStatus $status, string $textStatus, array $errors = [], ?string $description = null): JsonResponse;
}