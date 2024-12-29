<?php

declare(strict_types = 1);

namespace App\ApiResource\Order;

use ApiPlatform\Doctrine\Orm\State\Options;
use ApiPlatform\Metadata\ApiProperty;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Delete;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Patch;
use ApiPlatform\Metadata\Post;
use App\ApiResource\ShippingAddress\ShippingAddressApi;
use App\ApiResource\ShippingMethod\ShippingMethodApi;
use App\ApiResource\User\UserApi;
use App\Entity\Order;
use App\State\DtoToEntityStateProcessor;
use App\State\EntityToDtoStateProvider;
use Symfony\Component\Validator\Constraints\NotBlank;

#[ApiResource (
    shortName: 'Order',
    description: 'Order placed by the user',
    operations: [
        new Get(),
        new GetCollection(),
        new Post(),
        new Patch(
            validationContext: ['groups' => 'Default', 'patchValidation']
        ),
        new Delete(),
    ],
    security: 'is_granted("ROLE_USER")',
    provider: EntityToDtoStateProvider::class,
    processor: DtoToEntityStateProcessor::class,
    stateOptions: new Options(entityClass: Order::class),
)]
class OrderApi
{

    #[ApiProperty(readable: false, writable: false, identifier: true)]
    public ?int $id = null;

    public ?UserApi $ownedBy = null;

    public ?string $status = null;

    public ?string $totalPrice = null;

    public ?string $couponCode = null;

    public ?ShippingAddressApi $shippingAddress = null;

    public ?string $paymentStatus = null;

    public ?ShippingMethodApi $shippingMethod = null;

    public ?\DateTimeImmutable $createdAt = null;

    #[NotBlank(groups: ['patchValidation'])]
    public ?\DateTimeImmutable $updatedAt = null;
}