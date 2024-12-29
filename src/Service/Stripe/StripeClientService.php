<?php

declare(strict_types = 1);

namespace App\Service\Stripe;

use Stripe\Charge;
use Stripe\Checkout\Session;
use Stripe\Exception\ApiErrorException;
use Stripe\PaymentIntent;
use Stripe\Stripe;

class StripeClientService
{

    private string $sessionId;

    public function __construct (
        private readonly string $apiSecretKey,
    )
    {
        Stripe::setApiKey($this->apiSecretKey);
    }

    public function getTransactionId(): string
    {
        return $this->getFullCharge()->id;
    }

    public function getChargedAmountInCents(): int
    {
        return $this->getFullCharge()->amount;
    }

    public function getCurrency(): string
    {
        return $this->getFullCharge()->currency;
    }

    public function getPaymentMethodType(): string
    {
        return $this->getFullCharge()->payment_method_details->type;
    }

    public function getPaymentStatus(): string
    {
        return $this->getFullCharge()->status === 'succeeded' ? 'paid' : 'not_paid';
    }

    public function getFullCharge(): Charge|null
    {
        return $this->getCharge (
            $this->getPaymentIntent ($this->getSession())
        );
    }

    public function getCharge(PaymentIntent $paymentIntent): Charge|null
    {
        return $paymentIntent ? Charge::retrieve($paymentIntent->latest_charge) : null;
    }

    /**
     * @throws ApiErrorException
     */
    public function getSession(): Session
    {
        return Session::retrieve($this->getSessionId(), [
            'expand' => ['payment_intent']
        ]);
    }

    public function getPaymentIntent(Session $session): PaymentIntent|null
    {
        return $session->payment_intent ? PaymentIntent::retrieve($session->payment_intent) : null;
    }

    public function setSessionId(string $sessionId): void
    {
        $this->sessionId = $sessionId;
    }

    public function getSessionId(): string
    {
        return $this->sessionId;
    }
}