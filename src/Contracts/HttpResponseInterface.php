<?php

declare(strict_types = 1);

namespace App\Contracts;

use Symfony\Component\HttpFoundation\JsonResponse;

interface HttpResponseInterface
{
    public function forbiddenResponse(string $textStatus): JsonResponse;
    public function unauthorizedResponse(string $textStatus): JsonResponse;
    public function successResponse(string $textStatus): JsonResponse;
    public function validationErrorResponse(array $errors, string $textStatus): JsonResponse;
    public function notFoundException(string $textStatus): JsonResponse;
    public function invalidArgumentException(string $textStatus): JsonResponse;

    public function fileNotFoundException(string $textStatus, ?string $description = null): JsonResponse;
    public function fileTooLargeException(string $textStatus, ?string $description = null): JsonResponse;
    public function invalidFileException(string $textStatus, ?string $description  = null): JsonResponse;

    public function serverError(array $errors, string $textStatus, ?string $description = null,): JsonResponse;
}