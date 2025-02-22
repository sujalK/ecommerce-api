<?php

declare(strict_types = 1);

namespace App\Validator;

use App\ApiResource\Product\ProductApi;
use App\Repository\InventoryRepository;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

class IsUniqueProductInInventoryValidator extends ConstraintValidator
{

    public function __construct (
        private readonly InventoryRepository $inventoryRepository,
    )
    {
    }

    public function validate(mixed $value, Constraint $constraint): void
    {
        /* @var IsUniqueProductInInventory $constraint */

        if (null === $value || '' === $value) {
            return;
        }

        assert($value instanceof ProductApi);

        $product = $this->inventoryRepository->findOneBy(['product' => $value->id]);

        if ($product) {
            $this->context->buildViolation($constraint->message)
                ->addViolation();
        }

    }
}
