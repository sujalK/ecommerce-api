<?php

declare(strict_types = 1);

namespace App\Http;

use App\Contracts\HttpResponseInterface;
use App\Exception\AuthenticationException;
use App\Exception\FileNotFoundException;
use App\Exception\FileTooLargeException;
use App\Exception\InvalidFileException;
use App\Exception\InventoryException\ProductNotFoundException;
use App\Exception\UnauthorizedException;
use InvalidArgumentException;
use Symfony\Component\HttpFoundation\JsonResponse;
use Throwable;

class ProductImageUpdateExceptionHandler
{

    public function __construct (
        private readonly HttpResponseInterface $response,
    )
    {
    }

    /**
     * Handle the exception for Replacing image of a product
     */
    public function handle(Throwable $e): JsonResponse
    {
        return match(get_class($e)) {
            ProductNotFoundException::class => $this->response->notFoundException(),
            UnauthorizedException::class    => $this->response->forbiddenResponse(),
            AuthenticationException::class  => $this->response->unauthorizedResponse(),
            InvalidArgumentException::class => $this->response->invalidArgumentException(),
            FileNotFoundException::class    => $this->response->fileNotFoundException(description: 'File not uploaded. Please make sure to upload the file.'),
            FileTooLargeException::class    => $this->response->fileTooLargeException(description: 'File too large, max size is 5 MB.'),
            InvalidFileException::class     => $this->response->invalidFileException(description: 'Invalid file uploaded.'),
            default => $this->response->serverError(description: 'Something went wrong. Please try again later'),
        };
    }

}