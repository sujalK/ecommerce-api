<?php

declare(strict_types = 1);

namespace App\Validator;

use App\ApiResource\Inventory\InventoryApi;
use App\Entity\Inventory;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfonycasts\MicroMapper\MicroMapperInterface;

class NotAllowedProductChangeInInventoryValidator extends ConstraintValidator
{

    public function __construct (
        private readonly EntityManagerInterface $entityManager,
        private readonly MicroMapperInterface $microMapper,
    )
    {
    }

    public function validate(mixed $value, Constraint $constraint): void
    {
        /* @var NotAllowedProductChangeInInventory $constraint */

        if (null === $value || '' === $value) {
            return;
        }

        assert($value instanceof InventoryApi);

        // convert $value (which is InventoryApi instance/object) to Inventory entity
        $inventory = $this->microMapper->map($value, Inventory::class, [
            MicroMapperInterface::MAX_DEPTH => 0,
        ]);

        // get the unit of work
        $unitOfWork = $this->entityManager->getUnitOfWork();

        $originalData      = $unitOfWork->getOriginalEntityData($inventory);
        $originalProductId = $originalData['product']->getId();

        // get new product id
        $newProductId = $value->product->id;

        // if original product id does not match with new productId, then create validation error
        if ($originalProductId !== $newProductId) {
            $this->context->buildViolation($constraint->message)
                ->addViolation();
        }

    }
}
