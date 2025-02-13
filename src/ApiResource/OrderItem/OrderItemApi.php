<?php

declare(strict_types = 1);

namespace App\ApiResource\OrderItem;

use ApiPlatform\Doctrine\Orm\State\Options;
use ApiPlatform\Metadata\ApiProperty;
use ApiPlatform\Metadata\ApiResource;
use App\ApiResource\Order\OrderApi;
use App\ApiResource\Product\ProductApi;
use App\Entity\OrderItem;
use App\State\DtoToEntityStateProcessor;
use App\State\EntityToDtoStateProvider;

#[ApiResource (
    shortName: 'OrderItem',
    description: 'Item that belongs to an Order',
    provider: EntityToDtoStateProvider::class,
    processor: DtoToEntityStateProcessor::class,
    stateOptions: new Options(entityClass: OrderItem::class)
)]
class OrderItemApi
{

    #[ApiProperty(readable: false, writable: false, identifier: true)]
    public ?int $id                         = null;

    public ?OrderApi $order                 = null;

    public ?ProductApi $product             = null;

    public ?int $quantity                   = null;

    public ?string $unitPrice               = null;

    public ?string $totalPrice              = null;

    public ?string $unitPriceAfterDiscount  = null;

    public ?string $totalPriceAfterDiscount = null;

    public ?\DateTimeImmutable $createdAt   = null;

    public ?\DateTimeImmutable $updatedAt   = null;

}