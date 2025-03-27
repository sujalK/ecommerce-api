<?php

declare(strict_types = 1);

namespace App\Validator;

use App\ApiResource\User\UserApi;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfonycasts\MicroMapper\MicroMapperInterface;

class IsOriginalUserNameValidator extends ConstraintValidator
{

    public function __construct (
        private readonly EntityManagerInterface $entityManager,
        private readonly MicroMapperInterface $microMapper,
    )
    {
    }

    public function validate(mixed $value, Constraint $constraint): void
    {
        /* @var IsOriginalUserName $constraint */

        if (null === $value || '' === $value) {
            return;
        }

        assert($value instanceof UserApi);

        $userEntity = $this->microMapper->map($value, User::class, [
            MicroMapperInterface::MAX_DEPTH => 0,
        ]);

        $unitOfWork     = $this->entityManager->getUnitOfWork();
        $originalEntity = $unitOfWork->getOriginalEntityData($userEntity);

        $originalUserName = $originalEntity['username'];
        $newUserName      = $value->userName;

        if ($originalUserName !== $newUserName) {
            $this->context->buildViolation($constraint->message)
                ->addViolation();
        }
    }
}
