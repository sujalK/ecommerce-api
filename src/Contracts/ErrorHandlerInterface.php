<?php

declare(strict_types = 1);

namespace App\Contracts;

use Exception;
use Symfony\Component\HttpFoundation\Response;

interface ErrorHandlerInterface
{
    public function handleCartError(Exception $e): Response;
}