<?php

declare(strict_types = 1);

namespace App\Contracts;

interface RequestDataUtilsInterface
{
    public function keyExistsInRequestData(string $key): bool;

    public function setUpdatedAt(object $object): void;
}