<?php

declare(strict_types = 1);

namespace App\EventListener;

use ApiPlatform\Metadata\Exception\ItemNotFoundException;
use ApiPlatform\Validator\Exception\ValidationException;
use App\Exception\CouponExpiredException;
use App\Exception\CouponNotFoundException;
use App\Exception\MaxShippingAddressReachedException;
use App\Exception\MissingOrderItemsException;
use App\Exception\PendingOrderNotFoundException;
use Stripe\Exception\ApiErrorException;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Serializer\Exception\UnexpectedValueException;


class ApiExceptionListener implements EventSubscriberInterface
{

    public function __construct (
        private readonly RequestStack $requestStack,
    )
    {
    }

    public function onKernelException(ExceptionEvent $event): void
    {
        $exception = $event->getThrowable();
        $response  = null;

        // get the current request
        $request       = $this->requestStack->getCurrentRequest();

        // get the request method
        $requestMethod = $request->getRealMethod();

        // During verification
        if (
            $requestMethod === 'GET' &&
            str_contains($request->getUri(), '/user/verify') &&
            str_contains($exception->getMessage(), 'App\Entity\User')
        ) {

            // set up the response
            $response = new JsonResponse([
                'statusCode' => 400,
                'error'      => 'Invalid request',
                'message'    => 'Bad Request',
            ], 400);

            // set response
            $event->setResponse($response);

            return;
        }

        /*
         * For HttpException, for non-authenticated user
         */
        if ( $exception instanceof HttpException ) {
            $response = new JsonResponse([
                'statusCode' => $exception->getStatusCode(),
                'success'    => false,
                'message'    => $exception->getMessage(),
            ]);
        }

        /*
         * PaymentProcessor-specific Exceptions
         */
        if ($exception instanceof CouponNotFoundException) {
            $response = new JsonResponse([
                'statusCode' => 400,
                'success'    => false,
                'message'    => 'Coupon not found',
            ], 400);
        }

        if ($exception instanceof CouponExpiredException) {
            $response = new JsonResponse([
                'statusCode' => 422,
                'success'    => false,
                'message'    => 'Coupon is expired.',
            ], 422);
        }

        if ($exception instanceof MissingOrderItemsException) {
            $response = new JsonResponse([
                'statusCode' => 400,
                'success'    => false,
                'message'    => 'No order items found for this order.',
            ], 400);
        }

        if ($exception instanceof PendingOrderNotFoundException) {
            $response = new JsonResponse([
                'statusCode' => 404,
                'success'    => false,
                'message'    => 'Pending order not found.',
            ], 404);
        }

        if ($exception instanceof ValidationException) {
            $property = explode(':', $exception->getMessage())[0];

            $response = new JsonResponse([
                'success'     => false,
                'message'     => $exception->getMessage(),
                'invalidKey'  => $property === $exception->getMessage() ? 'n/a' : $property,
                'description' => 'Invalid data',
            ], 422);
        }

        /*
         * ApiErrorException
         */
        if ($exception instanceof ApiErrorException) {
            $response = new JsonResponse([
                'statusCode'  => 500,
                'success'     => false,
                'message'     => 'Something went wrong.',
            ], 500);
        }

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
