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
use App\State\PlaceOrderStateProcessor;
use Symfony\Component\Validator\Constraints as Assert;

#[ApiResource (
    shortName: 'Order',
    description: 'Order placed by the user',
    operations: [
        new Get(),
        new GetCollection(),
        new Post (
            processor: PlaceOrderStateProcessor::class,
        ),
//        new Patch(
//            validationContext: ['groups' => 'Default', 'patchValidation']
//        ),
//        new Delete(),
    ],
    paginationItemsPerPage: 10,
    security: 'is_granted("ROLE_USER")',
    provider: EntityToDtoStateProvider::class,
    processor: DtoToEntityStateProcessor::class,
    stateOptions: new Options(entityClass: Order::class),
)]
class OrderApi
{

    #[ApiProperty(readable: true, writable: false, identifier: true)]
    public ?int $id = null;

    #[ApiProperty(writable: false)]
    public ?UserApi $ownedBy = null;

    #[ApiProperty(writable: false)]
    public string $status = 'pending';

    #[ApiProperty(writable: false)]
    public ?string $totalPrice = null;

    #[ApiProperty(writable: false)]
    public ?string $couponCode = null;

    #[Assert\NotNull]
    #[ApiProperty(writable: true)]
    public ?ShippingAddressApi $shippingAddress = null;

    #[ApiProperty(writable: false)]
    public ?string $paymentStatus = 'pending';

    public ?string $currency = null;

    #[Assert\NotNull]
    #[ApiProperty(writable: true)]
    public ?ShippingMethodApi $shippingMethod = null;

    public ?\DateTimeImmutable $createdAt = null;

    public ?\DateTimeImmutable $updatedAt = null;
}