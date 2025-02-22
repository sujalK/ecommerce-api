<?php

declare(strict_types = 1);

namespace App\Validator;

use App\ApiResource\Product\ProductApi;
use App\Entity\User;
use App\Repository\WishlistRepository;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

class IsUniqueProductValidator extends ConstraintValidator
{

    public function __construct (
        private readonly WishlistRepository $wishlistRepository,
        private readonly Security $security,
    )
    {
    }

    public function validate(mixed $value, Constraint $constraint): void
    {
        /* @var IsUniqueProduct $constraint */

        if (null === $value || '' === $value) {
            return;
        }

        assert($value instanceof ProductApi);

        $user = $this->security->getUser();
        assert($user instanceof User);

        $product = $this->wishlistRepository->findOneBy(['product' => $value->id, 'ownedBy' => $user]);

        // If the product is already in wishlist, create violation
        if ($product) {
            $this->context->buildViolation($constraint->message)
                ->addViolation();
        }
    }
}
