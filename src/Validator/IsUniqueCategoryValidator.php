<?php

declare(strict_types = 1);

namespace App\Validator;

use App\Repository\ProductCategoryRepository;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

class IsUniqueCategoryValidator extends ConstraintValidator
{

    public function __construct (
        private readonly ProductCategoryRepository $productCategoryRepository,
    )
    {
    }

    public function validate(mixed $value, Constraint $constraint): void
    {
        /* @var IsUniqueCategory $constraint */

        if (null === $value || '' === $value) {
            return;
        }

        /** @var string $categoryName */
        $categoryName = $value;

        $category = $this->productCategoryRepository->findOneBy(['categoryName' => $categoryName]);

        // If the category exists, then we need to make sure validation error occurs
        if ($category) {
            $this->context->buildViolation($constraint->message)
                          ->addViolation();
        }

    }
}
