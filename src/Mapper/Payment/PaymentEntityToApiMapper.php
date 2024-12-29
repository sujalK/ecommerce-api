<?php

declare(strict_types = 1);

namespace App\Mapper\Payment;

use App\ApiResource\Order\OrderApi;
use App\ApiResource\Payment\PaymentApi;
use App\Entity\Payment;
use Symfonycasts\MicroMapper\AsMapper;
use Symfonycasts\MicroMapper\MapperInterface;
use Symfonycasts\MicroMapper\MicroMapperInterface;

#[AsMapper(from: Payment::class, to: PaymentApi::class)]
class PaymentEntityToApiMapper implements MapperInterface
{

    public function __construct(
        private readonly MicroMapperInterface $microMapper,
    )
    {
    }

    public function load(object $from, string $toClass, array $context): object
    {
        $entity = $from;
        assert($entity instanceof Payment);

        $dto = new PaymentApi();
        $dto->id = $entity->getId();

        return $dto;
    }

    public function populate(object $from, object $to, array $context): object
    {
        $entity = $from;
        $dto    = $to;
        assert($entity instanceof Payment);
        assert($dto instanceof PaymentApi);

        $dto->order = $this->microMapper->map($entity->getOrder(), OrderApi::class, [
            MicroMapperInterface::MAX_DEPTH => 0,
        ]);
        $dto->paymentMethod   = $entity->getPaymentMethod();
        $dto->paymentStatus   = $entity->getPaymentStatus();
        $dto->amount          = $entity->getAmount();
        $dto->paymentDate     = $entity->getPaymentDate();
        $dto->transactionId   = $entity->getTransactionId();
        $dto->billingAddress  = $entity->getBillingAddress();
        $dto->lineItems       = $entity->getLineItems();
        $dto->stripeSessionId = $entity->getStripeSessionId();
        
        return $dto;
    }
}