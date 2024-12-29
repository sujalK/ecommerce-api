<?php

declare(strict_types = 1);

namespace App\ApiResource\User;

use ApiPlatform\Doctrine\Orm\State\Options;
use ApiPlatform\Metadata\ApiProperty;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Delete;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Patch;
use ApiPlatform\Metadata\Post;
use App\Entity\User;
use App\State\DtoToEntityStateProcessor;
use App\State\EntityToDtoStateProvider;
use Symfony\Component\Serializer\Attribute\SerializedName;
use Symfony\Component\Validator\Constraints as Assert;

#[ApiResource (
    shortName: 'User',
    description: 'Api Resource that belongs to User',
    operations: [
        new Get(
            security: 'is_granted("ROLE_USER_READ")'
        ),
        new GetCollection(
            security: 'is_granted("ROLE_USER_READ")'
        ),
        new Post (
            security: 'is_granted("PUBLIC_ACCESS")',
            validationContext: ['groups' => ['Default', 'postValidation']]
        ),
        new Patch(
            security: 'is_granted("ROLE_USER_EDIT")'
        ),
        new Delete(
            security: 'is_granted("ROLE_USER_DELETE")'
        ),
    ],
    paginationItemsPerPage: 5,
    security: 'is_granted("ROLE_USER")',
    provider: EntityToDtoStateProvider::class,
    processor: DtoToEntityStateProcessor::class,
    stateOptions: new Options(entityClass: User::class),
)]
class UserApi
{

    #[ApiProperty(readable: false, writable: false, identifier: true)]
    public ?int $id                   = null;

    #[Assert\Email]
    #[Assert\NotBlank]
    public ?string $email             = null;

    #[ApiProperty(readable: false)]
    #[Assert\NotBlank(groups: ['postValidation'])]
    public ?string $password          = null;

    #[Assert\Length (
        min: 3,
        max: 50,
        minMessage: 'Please make sure the username is at least 3 characters in length.',
        maxMessage: 'Please make sure the username is at most 50 characters in length.'
    )]
    #[Assert\Regex (
        pattern: '/^[a-zA-Z0-9_]+$/',
        message: 'The username must be alphanumeric.',
    )]
    #[SerializedName('username')]
    public ?string $userName          = null;

    #[Assert\Regex(
        pattern: '/^[a-zA-Z]+$/',
        message: 'Please make sure the firstName is valid.'
    )]
    public ?string $firstName         = null;

    #[Assert\Regex(
        pattern: '/^[a-zA-Z]+$/',
        message: 'Please make sure the firstName is valid.'
    )]
    public ?string $lastName          = null;

    #[Assert\Type(type: 'bool', message: 'The value must be either true/false')]
    public ?bool $accountActiveStatus = null;

    #[Assert\Type(type: 'bool', message: 'The value must be either true/false')]
    public ?bool $verificationStatus  = null;


//    #[ApiProperty(writable: false)]
//    public ?array $carts              = null;
//
//    #[ApiProperty(writable: false)]
//    public ?array $shippingAddresses  = null;
//
//    #[ApiProperty(writable: false)]
//    public ?array $orders             = null;

}