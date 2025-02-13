<?php

declare(strict_types = 1);

namespace App\Service;

use App\Contracts\ErrorHandlerInterface;
use App\Contracts\HttpResponseInterface;
use App\Exception\CouponExpiredException;
use App\Exception\CouponNotFoundException;
use App\Exception\InvalidCouponException;
use Symfony\Component\HttpFoundation\Response;
use Throwable;

class CouponErrorHandlerService implements ErrorHandlerInterface
{

    public function __construct(
        private readonly HttpResponseInterface $httpResponse,
    )
    {
    }

    public function handleError(Throwable $e): Response
    {
        return match(true) {
            $e instanceof CouponNotFoundException => $this->httpResponse->invalidDataResponse(errors: $e->getErrors()),
            $e instanceof InvalidCouponException  => $this->httpResponse->invalidDataResponse(errors: $e->getErrors()),
            $e instanceof CouponExpiredException  => $this->httpResponse->invalidDataResponse(errors: $e->getErrors()),
            default => $this->httpResponse->invalidDataResponse(errors: ['Something went wrong.'])
        };
    }
}