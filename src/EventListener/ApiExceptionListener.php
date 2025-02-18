<?php

declare(strict_types = 1);

namespace App\EventListener;

use ApiPlatform\Metadata\Exception\ItemNotFoundException;
use App\Exception\MaxShippingAddressReachedException;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Serializer\Exception\UnexpectedValueException;


class ApiExceptionListener implements EventSubscriberInterface
{
    public function onKernelException(ExceptionEvent $event): void
    {
        $exception = $event->getThrowable();
        $response  = null;

        // For max shippingAddressException
        if ($exception instanceof MaxShippingAddressReachedException) {
            $response = new JsonResponse([
                'success'     => false,
                'message'     => 'Too many shippingAddresses',
                'description' => 'Please make sure to edit the existing one. Max shipping address reached.',
            ], 422);
        }

        // Catch the serializer's unexpected value exception
        if ($exception instanceof UnexpectedValueException) {
            // When we send non-existing value during request, we get this exception from our serializer
            $response = new JsonResponse([
                'status'  => 400,
                'error'   => 'Bad Request',
                'message' => $exception->getMessage(),
            ], 400);
        }
        // Catch API Platform's ItemNotFoundException (if applicable)
        elseif ($exception instanceof ItemNotFoundException) {
            $response = new JsonResponse([
                'status'  => 404,
                'error'   => 'Resource not found',
                'message' => $exception->getMessage(),
            ], 404);
        }
        // Catch Symfony's NotFoundHttpException (fallback)
        elseif ($exception instanceof NotFoundHttpException) {
            $response = new JsonResponse([
                'status'  => 404,
                'error'   => 'Resource not found',
                'message' => $exception->getMessage(),
            ], 404);
        }

        if ($response) {
            $event->setResponse($response);
        }

    }

    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::EXCEPTION => 'onKernelException',
        ];
    }
}
