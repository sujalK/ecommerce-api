<?php

declare(strict_types = 1);

namespace App\Service\HttpResponse;

use App\Contracts\HttpResponseInterface;
use App\Contracts\Json\JsonResponderInterface;
use App\Enum\HttpStatus;
use Symfony\Component\HttpFoundation\JsonResponse;

class GenericHttpResponse implements HttpResponseInterface
{

    private const SUCCESS = 'success';
    private const ERROR   = 'error';

    public function __construct (
        private readonly JsonResponderInterface $jsonResponder,
    )
    {
    }

    public function forbiddenResponse(string $textStatus = self::ERROR): JsonResponse
    {
        return $this->jsonResponder->createResponse(HttpStatus::FORBIDDEN, $textStatus);
    }

    public function unauthorizedResponse(string $textStatus = self::ERROR): JsonResponse
    {
        return $this->jsonResponder->createResponse(HttpStatus::UNAUTHORIZED, $textStatus);
    }

    public function successResponse(string $textStatus = self::SUCCESS): JsonResponse
    {
        return $this->jsonResponder->createResponse(HttpStatus::OK, $textStatus);
    }

    public function validationErrorResponse(array $errors = [], string $textStatus = self::ERROR): JsonResponse
    {
        return $this->jsonResponder->createResponse(HttpStatus::UNPROCESSABLE_ENTITY, $textStatus, $errors);
    }

    public function notFoundException(string $textStatus = self::ERROR, ?string $description = null,): JsonResponse
    {
        return $this->jsonResponder->createResponse(status: HttpStatus::NOT_FOUND, textStatus: $textStatus, description: $description);
    }

    public function invalidArgumentException(string $textStatus = self::ERROR): JsonResponse
    {
        return $this->jsonResponder->createResponse(HttpStatus::BAD_REQUEST, $textStatus);
    }

    public function fileNotFoundException(string $textStatus = self::ERROR, ?string $description = null): JsonResponse
    {
        return $this->jsonResponder->createResponse(status: HttpStatus::BAD_REQUEST, textStatus: $textStatus, description: $description);
    }

    public function fileTooLargeException(string $textStatus = self::ERROR, ?string $description = null): JsonResponse
    {
        return $this->jsonResponder->createResponse(status: HttpStatus::BAD_REQUEST, textStatus: $textStatus, description: $description);
    }

    public function invalidFileException(string $textStatus = self::ERROR, ?string $description = null): JsonResponse
    {
        return $this->jsonResponder->createresponse(status: HttpStatus::BAD_REQUEST, textStatus: $textStatus, description: $description);
    }

    public function serverError(array $errors = [], string $textStatus = self::ERROR, ?string $description = null,): JsonResponse
    {
        return $this->jsonResponder->createResponse(status: HttpStatus::INTERNAL_SERVER_ERROR, textStatus: $textStatus, errors: $errors, description: $description);
    }

    public function invalidDataResponse(string $textStatus = self::ERROR, array $errors = [], ?string $description = null): JsonResponse
    {
        return $this->jsonResponder->createresponse(status: HttpStatus::BAD_REQUEST, textStatus: $textStatus, errors: $errors, description: $description);
    }
}