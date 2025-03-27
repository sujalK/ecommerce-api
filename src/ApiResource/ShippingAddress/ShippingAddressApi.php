<?php

declare(strict_types = 1);

namespace App\ApiResource\ShippingAddress;

use ApiPlatform\Doctrine\Orm\State\Options;
use ApiPlatform\Metadata\ApiProperty;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Delete;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Patch;
use ApiPlatform\Metadata\Post;
use App\ApiResource\Order\OrderApi;
use App\ApiResource\User\UserApi;
use App\Entity\ShippingAddress;
use App\State\DtoToEntityStateProcessor;
use App\State\EntityToDtoStateProvider;
use App\State\ShippingAddressStateProcessor;
use Symfony\Component\Validator\Constraints as Assert;

#[ApiResource(
    shortName: 'ShippingAddress',
    description: 'Shipping Address',
    operations: [
        new Get (
            security: 'user !== null and (object.owner.id === user.getId() or is_granted("ROLE_ADMIN"))'
        ),
        new GetCollection(),
        new Post (
            validationContext: ['groups' => ['Default', 'postValidation']]
        ),
        new Patch (
            security: 'is_granted("EDIT", object)',
        ),
        new Delete (
            security: 'is_granted("DELETE", object)',
        ),
    ],
    security: 'is_granted("ROLE_USER")',
    provider: EntityToDtoStateProvider::class,
    processor: ShippingAddressStateProcessor::class,
    stateOptions: new Options(entityClass: ShippingAddress::class)
)]
class ShippingAddressApi
{

    #[ApiProperty(readable: false, writable: false, identifier: true)]
    public ?int $id                       = null;

    #[ApiProperty(readable: true, writable: false)]
    public ?UserApi $owner                = null;

    #[Assert\NotBlank(groups: ['postValidation'])]
    #[Assert\Regex(
        pattern: '/^[a-zA-Z0-9\s\-,\.\/]+$/',
        message: 'Address line 1 contains invalid characters.'
    )]
    public ?string $addressLine1          = null;

    #[Assert\Regex(
        pattern: '/^[a-zA-Z0-9\s\-,\.]*$/',
        message: 'Address line 2 contains invalid characters.'
    )]
    public ?string $addressLine2          = null;

    #[Assert\NotBlank(groups: ['postValidation'])]
    #[Assert\Regex(
        pattern: '/^[a-zA-Z\s\-]+$/',
        message: 'City must only contain letters, spaces, or hyphens.'
    )]
    public ?string $city                  = null;

    #[Assert\NotBlank(groups: ['postValidation'])]
    public ?string $state                 = null;

    #[Assert\NotBlank(groups: ['postValidation'])]
    #[Assert\Regex(
        pattern: '/^[0-9]+$/',
        message: 'Postal code must be numeric.'
    )]
    public ?string $postalCode            = null;

    #[Assert\NotBlank(groups: ['postValidation'])]
    #[Assert\Regex(
        pattern: '/^[A-Za-z1]+$/',
        message: 'Country must be a valid string.'
    )]
    #[Assert\Length(
        min: 2,
        max: 50,
        minMessage: 'Invalid country name',
        maxMessage: 'Please make sure to enter correct name of the country',
    )]
    public ?string $country               = null;

    #[Assert\NotBlank(groups: ['postValidation'])]
    #[Assert\Regex(
        pattern: '/^[0-9]+$/',
    )]
    #[Assert\Length(
        max: 15,
        maxMessage: 'Invalid phone number'
    )]
    public ?string $phoneNumber           = null;

    #[ApiProperty(readable: false, writable: false)]
    public ?\DateTimeImmutable $createdAt = null;

    #[ApiProperty(readable: false, writable: false)]
    public ?\DateTimeImmutable $updatedAt = null;

//    /**
//     * @var OrderApi[]
//     */
//    #[ApiProperty(readable: true, writable: false)]
//    public ?array $orders                 = null;

}