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
use App\State\UserStateProcessor;
use App\Validator\IsOriginalEmail;
use App\Validator\IsOriginalUserName;
use App\Validator\IsUniqueUserName;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Serializer\Attribute\SerializedName;
use Symfony\Component\Validator\Constraints as Assert;

#[ApiResource (
    shortName: 'User',
    description: 'Api Resource that belongs to User',
    operations: [
        new Get(
            security: 'is_granted("VIEW", object)'
        ),
        new GetCollection(
            security: 'is_granted("ROLE_ADMIN")'
        ),
        new Post (
            security: 'is_granted("PUBLIC_ACCESS")',
            validationContext: ['groups' => ['Default', 'postValidation']]
        ),
        new Patch(
            security: 'is_granted("EDIT", object)',
            validationContext: ['groups' => ['Default', 'patchValidation']]
        ),
        new Delete(
            security: 'is_granted("DELETE", object)'
        ),
    ],
    paginationItemsPerPage: 5,
    // security: 'is_granted("ROLE_USER")',
    provider: EntityToDtoStateProvider::class,
    // processor: DtoToEntityStateProcessor::class,
    processor: UserStateProcessor::class,
    stateOptions: new Options(entityClass: User::class),
)]
#[UniqueEntity(
    fields: ['email'],
    message: 'User already exists!',
    entityClass: User::class,
    groups: ['postValidation']
)]
#[IsOriginalEmail(groups: ['patchValidation'])]
#[IsOriginalUserName(groups: ['patchValidation'])]
class UserApi
{

    #[ApiProperty(readable: true, writable: false, identifier: true)]
    public ?int $id                   = null;

    #[Assert\Email]
    #[Assert\NotBlank(groups: ['postValidation'])]
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
    #[Assert\NotBlank(groups: ['postValidation'])]
    #[IsUniqueUserName(groups: ['postValidation'])]
    public ?string $userName          = null;

    #[Assert\Regex(
        pattern: '/^[a-zA-Z]+$/',
        message: 'Please make sure the firstName is valid.'
    )]
    #[Assert\Length (
        min: 2,
        max: 150,
        minMessage: 'Please make sure the firstName is at least 2 characters in length.',
        maxMessage: 'Please make sure the firstName is at most 150 characters in length.'
    )]
    #[Assert\NotBlank(groups: ['postValidation'])]
    public ?string $firstName         = null;

    #[Assert\Regex(
        pattern: '/^[a-zA-Z]+$/',
        message: 'Please make sure the firstName is valid.'
    )]
    #[Assert\Length (
        min: 2,
        max: 150,
        minMessage: 'Please make sure the lastName is at least 2 characters in length.',
        maxMessage: 'Please make sure the lastNme is at most 150 characters in length.'
    )]
    #[Assert\NotBlank(groups: ['postValidation'])]
    public ?string $lastName          = null;

    public ?array $roles              = [];

    #[Assert\Type(type: 'bool', message: 'The value must be either true/false')]
    #[ApiProperty(readable: true, writable: false)]
    public ?bool $isActive            = false;

    #[Assert\Type(type: 'bool', message: 'The value must be either true/false')]
    #[ApiProperty(readable: true, writable: false)]
    public ?bool $isVerified          = false;

    #[ApiProperty(readable: false, writable: false)]
    public ?string $verificationToken = null;

    #[ApiProperty(readable: false, writable: false)]
    public ?\DateTimeImmutable $verifiedAt = null;

//    #[ApiProperty(writable: false)]
//    public ?array $carts              = null;
//
//    #[ApiProperty(writable: false)]
//    public ?array $shippingAddresses  = null;
//
//    #[ApiProperty(writable: false)]
//    public ?array $orders             = null;

}