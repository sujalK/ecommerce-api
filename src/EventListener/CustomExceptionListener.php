<?php

declare(strict_types=1);

namespace App\EventListener;

use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use App\Exception\InvalidDataException;

class CustomExceptionListener
{
    public function onKernelException(ExceptionEvent $event): void
    {
        $exception = $event->getThrowable();

        // Handle specific exception types
        if ($exception instanceof InvalidDataException) {
            $response = new JsonResponse([
                'error' => 'Invalid data provided',
                'message' => $exception->getMessage(),
            ], JsonResponse::HTTP_BAD_REQUEST);

            $event->setResponse($response);

            return;
        }

        if ($exception instanceof NotFoundHttpException) {
            $response = new JsonResponse([
                'error' => 'Resource not found',
                'message' => $exception->getMessage(),
            ], JsonResponse::HTTP_NOT_FOUND);

            $event->setResponse($response);

            return;
        }

        // Default handling for other exceptions
        if ($exception instanceof HttpExceptionInterface) {
            $response = new JsonResponse([
                'error' => 'An error occurred',
                'message' => $exception->getMessage(),
            ], $exception->getStatusCode());
        } else {
            $response = new JsonResponse([
                'error' => 'An unexpected error occurred',
                'message' => $exception->getMessage(),
            ], JsonResponse::HTTP_INTERNAL_SERVER_ERROR);
        }

        $event->setResponse($response);
    }
}
