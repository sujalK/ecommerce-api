<?php

declare(strict_types = 1);

namespace App\Service;

use App\Contracts\PersistenceServiceInterface;
use Doctrine\ORM\EntityManagerInterface;

class PersistenceService implements PersistenceServiceInterface
{

    public function __construct (
        private readonly EntityManagerInterface $entityManager,
    )
    {
    }

    public function sync(?object $entity = null): void
    {
        if ( $entity ) {
            $this->entityManager->persist($entity);
        }

        $this->flush();
    }

    public function persist(object $object): void
    {
        $this->entityManager->persist($object);
    }

    public function flush(): void
    {
        $this->entityManager->flush();
    }
}