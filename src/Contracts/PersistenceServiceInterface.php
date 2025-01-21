<?php

declare(strict_types = 1);

namespace App\Contracts;

interface PersistenceServiceInterface
{
    public function persist(object $object): void;

    public function flush(): void;

    public function sync(object $entity = null): void;
}