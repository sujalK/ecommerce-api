<?php

declare(strict_types = 1);

namespace App\Service\Json;

use App\Contracts\Json\JsonResponderInterface;
use App\DataObjects\ErrorResponse\ResponseData;
use App\Enum\HttpStatus;
use Symfony\Component\HttpFoundation\JsonResponse;

class JsonResponder implements JsonResponderInterface
{

    public function createResponse(HttpStatus $status, string $textStatus = '', array $errors = [], ?string $description = null): JsonResponse
    {
        return new JsonResponse (
            data: $this->getArrayFromObject($status, $textStatus, $errors, $description),
            status: $this->getStatusCode($status)
        );
    }

    private function getArrayFromObject(HttpStatus $status, string $textStatus, array $errors = [], ?string $description = null): array
    {
        return get_object_vars (
            ResponseData::withDefaults (
                $status->toString(),
                $textStatus,
                $status,
                $errors,
                $description
            )
        );
    }

    private function getStatusCode(HttpStatus $status): int
    {
        return $status->value;
    }

}