<?php

declare(strict_types = 1);

namespace App\ApiResource\Payment;

use ApiPlatform\Doctrine\Orm\State\Options;
use ApiPlatform\Metadata\ApiProperty;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Delete;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Link;
use ApiPlatform\Metadata\Patch;
use ApiPlatform\Metadata\Post;
use App\ApiResource\Order\OrderApi;
use App\Entity\Order;
use App\Entity\Payment;
use App\State\EntityToDtoStateProvider;
use App\State\PaymentProcessor;

#[ApiResource(
    shortName: 'Payment',
    description: 'Payment resource',
    operations: [
        new Get(),
        new GetCollection(),
        new Post(
            input: false
        ),
        new Post (
            uriTemplate: '/payments/{id}',
            input: false,
            processor: PaymentProcessor::class
        )
    ],
    paginationItemsPerPage: 10,
    security: 'is_granted("ROLE_USER")', // Makes sure user is logged-in to perform HTTP operation
    provider: EntityToDtoStateProvider::class,
    processor: PaymentProcessor::class,
    stateOptions: new Options(entityClass: Payment::class),
)]
class PaymentApi
{
    #[ApiProperty(readable: true, writable: false, identifier: true)]
    public ?int $id                         = null;

    #[ApiProperty(writable: false)]
    public ?OrderApi $order                 = null;

    #[ApiProperty(writable: false)]
    public ?string $paymentMethod           = null;

    #[ApiProperty(writable: false)]
    public ?string $paymentStatus           = null;

    #[ApiProperty(writable: false)]
    public ?string $amount                  = null;
    
    #[ApiProperty(writable: false)]
    public ?\DateTimeImmutable $paymentDate = null;

    #[ApiProperty(writable: false)]
    public ?string $transactionId           = null;

    #[ApiProperty(writable: false)]
    public ?string $billingAddress          = null;

    #[ApiProperty(writable: false)]
    public ?array $lineItems                = null;

    // Add the Stripe session ID property
    #[ApiProperty(readable: true, writable: false)]
    public ?string $stripeSessionId = null; // The Stripe session ID
}