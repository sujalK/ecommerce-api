<?php

namespace App\Validator;

use App\ApiResource\Product\ProductApi;
use App\Repository\ProductRepository;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

class IsValidProductValidator extends ConstraintValidator
{

    public function __construct (
        private readonly ProductRepository $productRepository,
    )
    {
    }

    public function validate(mixed $value, Constraint $constraint): void
    {
        /* @var IsValidProduct $constraint */

        if (null === $value || '' === $value) {
            return;
        }

        assert($value instanceof ProductApi);

        $productId = $value->id;
        $product   = $this->productRepository->findOneBy(['id' => $productId]);

        // If the product does not exist with the given id
        // then it's invalid
        if ( ! $product ) {
            $this->context->buildViolation($constraint->message)
                          ->addViolation();
        }

    }
}
