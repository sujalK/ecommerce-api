<?php

declare(strict_types = 1);

namespace App\Factories;

use App\Contracts\CloudServiceProviderInterface;
use App\Entity\Product;
use Symfony\Component\HttpFoundation\JsonResponse;

class ProductResponseFactory
{

    public function __construct (
        private readonly CloudServiceProviderInterface $cloudServiceProvider,
    )
    {
    }

    public function create(Product $product, string $productId, string $s3FileName): JsonResponse
    {
        return new JsonResponse([
            'id'           => $productId,
            'name'         => $product->getName(),
            'description'  => $product->getDescription(),
            'price'        => $product->getPrice(),
            'category'     => '/api/categories/'. $product->getCategory()->getId(),
            'isActive'     => $product->isActive(),
            'productImage' => $this->cloudServiceProvider->getBucketUrl(key: $s3FileName, isPreSigned: false)
        ]);
    }

}