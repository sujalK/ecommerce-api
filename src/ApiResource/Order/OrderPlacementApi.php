<?php

declare(strict_types = 1);

namespace App\ApiResource\Order;

use ApiPlatform\Metadata\ApiProperty;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Post;
use Symfony\Component\Serializer\Attribute\Ignore;

#[ApiResource (
    shortName: 'Order',
    operations: [
        new Post(
            uriTemplate: '/orders',
            // processor:
        )
    ]
)]
class OrderPlacementApi
{

    #[ApiProperty(readable: false, writable: false, genId: false)]
    #[Ignore]
    public ?int $id = null;



}