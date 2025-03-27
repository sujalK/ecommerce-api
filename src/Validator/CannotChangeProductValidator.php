<?php

declare(strict_types = 1);

namespace App\Validator;

use App\ApiResource\Product\ProductApi;
use App\ApiResource\ProductReview\ProductReviewApi;
use App\Entity\Product;
use App\Entity\ProductReview;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfonycasts\MicroMapper\MicroMapperInterface;

class CannotChangeProductValidator extends ConstraintValidator
{

    public function __construct (
        private readonly EntityManagerInterface $entityManager,
        private readonly MicroMapperInterface $microMapper,
    )
    {
    }

    public function validate(mixed $value, Constraint $constraint): void
    {
        /* @var CannotChangeProduct $constraint */

        if (null === $value || '' === $value) {
            return;
        }
        assert($value instanceof ProductReviewApi);

        $productReview = $this->microMapper->map($value, ProductReview::class, [
            MicroMapperInterface::MAX_DEPTH => 1,
        ]);

        // get UnitOfWork()
        $unitOfWork         = $this->entityManager->getUnitOfWork();
        $originalEntityData = $unitOfWork->getOriginalEntityData($productReview);

        $originalProductReviewId = $originalEntityData['product']->getId();
        $newProductReviewId      = $value->product->id;

        if ($originalProductReviewId !== $newProductReviewId) {
            $this->context->buildViolation($constraint->message)
                ->addViolation();
        }
    }
}
