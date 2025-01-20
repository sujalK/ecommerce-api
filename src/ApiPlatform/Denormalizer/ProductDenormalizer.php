<?php

declare(strict_types = 1);

namespace App\ApiPlatform\Denormalizer;

use App\ApiResource\Product\ProductApi;
use App\Exception\InvalidDataException;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\Serializer\Exception\NotNormalizableValueException;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;

class ProductDenormalizer implements DenormalizerInterface
{

    public function __construct (
        #[Autowire(service: 'api_platform.serializer.normalizer.item')]
        private DenormalizerInterface $itemNormalizer,
    )
    {
    }

    /**
     * Denormalizes data into a Product object.
     *
     * @param mixed  $data    The data to restore
     * @param string $type    The expected class to instantiate
     * @param string|null $format  The format being deserialized from
     * @param array  $context Options available to the denormalizer
     *
     * @return ProductApi
     *
     * @throws NotNormalizableValueException When the data cannot be denormalized
     */
    public function denormalize(mixed $data, string $type, ?string $format = null, array $context = []): mixed
    {

        // Validate and process the data as needed
        if (!is_array($data)) {
            throw new NotNormalizableValueException('Data expected to be an array.');
        }

        // Ensure 'isActive' is a boolean
        if (isset($data['isActive']) && !is_bool($data['isActive'])) {
            throw new InvalidDataException('isActive must be a boolean value');
            // throw new NotNormalizableValueException('The "isActive" field must be a boolean.');
        }

        return $this->itemNormalizer->denormalize($data, $type, $format, $context);
    }

    /**
     * Checks whether the given class and format are supported for denormalization.
     *
     * @param mixed  $data    The data to denormalize
     * @param string $type    The class to which the data should be denormalized
     * @param string|null $format  The format being deserialized from
     * @param array  $context Options available to the denormalizer
     *
     * @return bool
     */
    public function supportsDenormalization(mixed $data, string $type, ?string $format = null, array $context = []): bool
    {
        return $type === ProductApi::class;
    }

    /**
     * Returns the types supported by this denormalizer.
     *
     * @param string|null $format The format being deserialized from
     *
     * @return array
     */
    public function getSupportedTypes(?string $format): array
    {
        return [
            ProductApi::class => true,
        ];
    }
}