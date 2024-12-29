<?php

declare(strict_types=1);

namespace App\EventListener;

use ApiPlatform\Metadata\Exception\InvalidArgumentException;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;
use Symfony\Component\Serializer\Exception\UnexpectedValueException;

class ExceptionListener
{

    public function __construct (
        private readonly RequestStack $requestStack,
    )
    {
    }

    public function onKernelException(ExceptionEvent $event): void
    {
        $exception = $event->getThrowable();

        // Handle NotFoundHttpException for invalid IRI
        if ($exception instanceof NotFoundHttpException) {
            // Create custom response for invalid IRI (resource not found)
            $response = new JsonResponse(
                [
                    'status' => 'error',
                    'message' => 'The resource you requested was not found or the IRI is invalid.',
                ],
                Response::HTTP_NOT_FOUND
            );
            $event->setResponse($response);
            return;
        }

        // Handle validation errors (e.g., incorrect data format)
        if ($exception instanceof InvalidArgumentException) {
            $response = new JsonResponse(
                [
                    'status' => 'error',
                    'message' => 'The provided arguments are invalid.',
                ],
                Response::HTTP_BAD_REQUEST
            );
            $event->setResponse($response);
            return;
        }

        // Handle generic HTTP exceptions (catch all)
        if ($exception instanceof HttpExceptionInterface) {
            $response = new JsonResponse(
                [
                    'status' => 'error',
                    'message' => $exception->getMessage(),
                ],
                $exception->getStatusCode()
            );
            $event->setResponse($response);
            return;
        }

        // This is for the Invalid IRI passed
        if ( $exception instanceof UnexpectedValueException ) {
            $request = $this->requestStack->getCurrentRequest();

            // Get the invalid IRI from the request if available
            $iri = json_decode($request->getContent(), true)['product'] ?? null;

            $response = new JsonResponse (
                [
                    'status'      => 'error',
                    'message'     => \sprintf('Invalid IRI passed: %s', ($iri ?? '')),
                    'status_code' => 400,
                ],
                400
            );
            $event->setResponse($response);
            return;
        }

        // For other exceptions, provide a generic response
        $response = new JsonResponse(
            [
                'status' => 'error',
                'message' => 'An unexpected error occurred.',
            ],
            Response::HTTP_BAD_REQUEST
        );
        $event->setResponse($response);
    }
}
