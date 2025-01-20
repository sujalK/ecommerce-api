<?php

declare(strict_types = 1);

namespace App\ApiResource\Product;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Link;
use ApiPlatform\Metadata\Post;
use App\Controller\ProductImagePatchController;
use App\Entity\Product;

#[ApiResource (
    uriTemplate: '/products/{id}/image.{_format}',
    shortName: 'Product',
    operations: [
        new Post (
            controller: ProductImagePatchController::class,
        )
    ],
    uriVariables: [
        'id' => new Link (
            fromProperty: 'id',
            fromClass: Product::class,
        )
    ],
)]
class ProductImageApi
{
}