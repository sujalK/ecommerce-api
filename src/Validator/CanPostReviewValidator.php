<?php

declare(strict_types = 1);

namespace App\Validator;

use App\ApiResource\ProductReview\ProductReviewApi;
use App\Entity\User;
use App\Repository\OrderItemRepository;
use App\Repository\ProductReviewRepository;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

class CanPostReviewValidator extends ConstraintValidator
{

    public function __construct(
        private readonly Security $security,
        private readonly OrderItemRepository $orderItemRepository,
        private readonly ProductReviewRepository $productReviewRepository,
    )
    {
    }

    public function validate(mixed $value, Constraint $constraint): void
    {
        /* @var CanPostReview $constraint */

        if (null === $value || '' === $value) {
            return;
        }

        assert($value instanceof ProductReviewApi);

        $productId = $value->product->id;
        $user      = $this->security->getUser();
        assert($user instanceof User);

        $shippedOrderItem = $this->orderItemRepository->findOneByProductAndUserWithShippedStatus($productId, $user);
        $existingReview   = $this->productReviewRepository->findOneBy(['product' => $productId, 'owner' => $user]);

        if ($existingReview) {
            $this->context->buildViolation($constraint->duplicateReview)
                 ->addViolation();
        }

        // If the product is not shipped (finalized), then in that case, cause validation error
        if ($shippedOrderItem === null) {
            $this->context->buildViolation($constraint->message)
                 ->addViolation();
        }
    }
}
