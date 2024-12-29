<?php

declare(strict_types = 1);

namespace App\Service;

use App\Contracts\DateAndTimeInterface;
use App\DataObjects\StripePaymentData;
use App\Entity\Order;
use App\Entity\Payment;
use App\Exception\InvalidPaymentException;
use App\Service\Stripe\StripeClientService;
use Stripe\Charge;
use Stripe\Exception\ApiErrorException;

class SuccessfulPaymentService
{
    public function __construct (
        private readonly StripeClientService $stripeClient,
        private readonly DateAndTimeInterface $dateAndTime,
    )
    {
    }

    /**
     * @throws ApiErrorException
     * @throws InvalidPaymentException
     */
    public function processPaymentInfo(string $sessionId, Order $order, Payment $payment): void
    {
        $paymentInfo = $this->getPaymentInfoFromStripe($sessionId);
        assert($paymentInfo instanceof StripePaymentData);

        $this->updateOrderInfo($order, $paymentInfo);
        $this->updatePaymentInfo($payment, $order, $paymentInfo);
    }

    /**
     * @throws ApiErrorException
     * @throws InvalidPaymentException
     */
    public function getPaymentInfoFromStripe(string $sessionId): StripePaymentData
    {
        $this->stripeClient->setSessionId($sessionId);
        $session = $this->stripeClient->getSession();

        if ( $session->payment_status !== 'paid' ) {
            throw new InvalidPaymentException('Payment Unsuccessful.');
        }

        $charge = $this->stripeClient->getFullCharge();

        // handle declined payments
        $this->handleDeclinedPayments($charge);

        return new StripePaymentData (
            $charge->id,
            $charge->amount,
            $charge->currency,
            $charge->payment_method_details->type,
            $charge->status === 'succeeded' ? 'paid' : 'not_paid',
        );
    }

    private function updateOrderInfo(Order $order, StripePaymentData $paymentData): void
    {
        $order->setPaymentStatus($paymentData->status);
        $order->setCurrency($paymentData->currency);
        $order->setStatus('order_placed');
        $order->setUpdatedAt(new \DateTimeImmutable('now', $this->dateAndTime->getTimeZone()));
    }

    private function updatePaymentInfo(Payment $payment, Order $order, StripePaymentData $paymentData): void
    {
        $payment->setPaymentMethod($paymentData->paymentMethodType);
        $payment->setPaymentStatus($paymentData->status);
        $payment->setPaymentDate(new \DateTimeImmutable('now', $this->dateAndTime->getTimeZone()));
        $payment->setOrder($order);
        $payment->setAmount((string) $paymentData->amount); // This amount is in cents
        $payment->setBillingAddress('[ Address Line 1: '. $order->getShippingAddress()->getAddressLine1(). ']' .', [ Address Line 2: ' . $order->getShippingAddress()->getAddressLine2() . ' ]' );
        $payment->setTransactionId($paymentData->transactionId);
    }

    /**
     * @param Charge|null $charge
     * @return void
     * @throws InvalidPaymentException
     */
    public function handleDeclinedPayments(?Charge $charge): void
    {
        // Add handling for declined payments
        if ($charge->status === 'failed') {
            throw new InvalidPaymentException('Payment Declined.');
        }

        // Add logic for other declined statuses
        if ($charge->status !== 'succeeded') {
            throw new InvalidPaymentException('Payment was not successful.');
        }
    }
}