<?php

declare(strict_types=1);

namespace App\ApiPlatform\ExceptionsNormalizer;

use App\Exception\InvalidDataException;
use JetBrains\PhpStorm\ArrayShape;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class InvalidDataExceptionNormalizer implements NormalizerInterface
{
    /**
     * Normalizes an InvalidDataException into a structured error response.
     */
    public function normalize(mixed $exception, ?string $format = null, array $context = []): array
    {
        if (!$this->supportsNormalization($exception, $format)) {
            return [];
        }

        return [
            'type' => '/errors/invalid-data',
            'title' => 'Invalid Data',
            'detail' => $exception->getMessage(),
            'status' => Response::HTTP_BAD_REQUEST,
        ];
    }

    /**
     * Determines whether this normalizer can normalize the given data.
     */
    public function supportsNormalization(mixed $data, string $format = null, array $context = []): bool
    {
        dump('Checking normalization support for:', $data);
        return $data instanceof InvalidDataException;
    }

    /**
     * Returns the types supported by this normalizer.
     */
    #[ArrayShape([InvalidDataException::class => "bool"])] public function getSupportedTypes(?string $format): array
    {
        return [
            InvalidDataException::class => true,
        ];
    }
}
