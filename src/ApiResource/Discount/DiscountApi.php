<?php

declare(strict_types = 1);

namespace App\ApiResource\Discount;

use ApiPlatform\Metadata\ApiProperty;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Post;
use App\State\ApplyCouponStateProcessor;
use App\State\RemoveCouponStateProcessor;
use Symfony\Component\Validator\Constraints as Assert;

#[ApiResource (
    shortName: 'Discount',
    description: 'Applies discount to the Order (set coupon code) and set discount amount on order_item',
    operations: [
        new Post (
            uriTemplate: '/apply-discount',
            validationContext: ['groups' => ['Default', 'postValidation']],
            processor: ApplyCouponStateProcessor::class,
        ),
        new Post (
            uriTemplate: '/remove-coupon',
            input: false, // Disables the deserialization for the remove-coupon operation
            processor: RemoveCouponStateProcessor::class,
        ),
    ],
    security: 'is_granted("ROLE_USER")',
)]
class DiscountApi
{

    #[ApiProperty(readable: false, writable: false, genId: false)]
    public ?int $id = null;

    /*
     * Coupon code that the user enters to set coupon code into order table and also set discount amount
     */
    #[Assert\NotBlank(groups: ['postValidation'])]
    #[Assert\NotNull(groups: ['postValidation'])]
    #[Assert\Regex (
        pattern: "/^[a-zA-Z0-9]+$/",
        message: 'This field should only contain numbers and alphabets.',
        groups: ['postValidation'],
    )]
    public ?string $couponCode = null;

}