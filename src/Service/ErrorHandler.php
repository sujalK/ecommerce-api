<?php

declare(strict_types = 1);

namespace App\Service;

use App\Contracts\ErrorHandlerInterface;
use App\Exception\InvalidQuantityException;
use App\Exception\InventoryException\InsufficientStockException;
use App\Exception\InventoryException\ProductNotFoundException;
use Exception;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

class ErrorHandler implements ErrorHandlerInterface
{

    public function handleCartError(Exception $e): Response
    {
        if ($e instanceof ProductNotFoundException) {
            return new JsonResponse(['status' => 'error', 'message' => $e->getMessage()], Response::HTTP_BAD_REQUEST);
        } else if ($e instanceof InsufficientStockException) {
            return new JsonResponse(['status' => 'error', 'message' => $e->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
        } else if ($e instanceof InvalidQuantityException) {
            return new JsonResponse(['status' => 'error', 'message' => 'Invalid quantity', 'statusCode' => 400], Response::HTTP_BAD_REQUEST);
        } else {
            return new JsonResponse(['status' => 'error', 'message' => 'An unexpected error occurred.'],  Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

}