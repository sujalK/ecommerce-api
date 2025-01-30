<?php

declare(strict_types = 1);

namespace App\Service\Cart\ErrorHandler;

use App\Contracts\HttpResponseInterface;
use App\Exception\CartItemNotFoundException;
use App\Exception\CartNotFoundException;
use App\Exception\InventoryException\InsufficientStockException;
use App\Exception\ProductChangeNotAllowedException;
use Exception;
use Symfony\Component\HttpFoundation\JsonResponse;

class CartErrorHandler
{

    public function __construct (
        private readonly HttpResponseInterface $httpResponse,
    )
    {
    }

    public function handleError(Exception $e): JsonResponse
    {
        return match(true) {
            $e instanceof ProductChangeNotAllowedException  => $this->httpResponse->invalidDataResponse(description: 'Product cannot be changed during PATCH request.'),
            $e instanceof CartNotFoundException,
            $e instanceof CartItemNotFoundException         => $this->httpResponse->notFoundException(description: $e->getMessage()),
            $e instanceof InsufficientStockException        => $this->httpResponse->invalidDataResponse(description: 'Not enough stock available.'),
            default => $this->httpResponse->serverError(description: 'Something went wrong.'),
        };
    }

}