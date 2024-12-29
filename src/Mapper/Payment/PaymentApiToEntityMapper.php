<?php

declare(strict_types = 1);

namespace App\Mapper\Payment;

use App\ApiResource\Payment\PaymentApi;
use App\Entity\Order;
use App\Entity\Payment;
use App\Repository\PaymentRepository;
use Symfonycasts\MicroMapper\AsMapper;
use Symfonycasts\MicroMapper\MapperInterface;
use Symfonycasts\MicroMapper\MicroMapperInterface;

#[AsMapper(from: PaymentApi::class, to: Payment::class)]
class PaymentApiToEntityMapper implements MapperInterface
{

    public function __construct (
        private readonly PaymentRepository $repository,
        private readonly MicroMapperInterface $microMapper,
    )
    {
    }

    public function load(object $from, string $toClass, array $context): object
    {
        $dto = $from;
        assert($dto instanceof PaymentApi);

        $entity = $dto->id ? $this->repository->find($dto->id) : new Payment();

        if ( ! $entity ) {
            throw new \Exception (
                \sprintf('Payment with id "%d" not found', $dto->id)
            );
        }

        return $entity;
    }

    public function populate(object $from, object $to, array $context): object
    {
        $dto    = $from;
        $entity = $to;
        assert($dto instanceof PaymentApi);
        assert($entity instanceof Payment);
        // dd($dto);
//        $entity->setOrder (
//
//            $this->microMapper->map($dto->order, Order::class, [
//                MicroMapperInterface::MAX_DEPTH => 0,
//            ])
//        );

//        $entity->setPaymentMethod($dto->paymentMethod);
//        $entity->setPaymentStatus($dto->paymentStatus);
//        $entity->setAmount($dto->amount);
//        $entity->setPaymentDate($dto->paymentDate);
//        $entity->setTransactionId($dto->transactionId);
//        $entity->setBillingAddress($dto->billingAddress);

        $entity->setLineItems($dto->lineItems);
        $entity->setStripeSessionId($dto->stripeSessionId);
        $entity->setOrder(
            $this->microMapper->map($dto->order, Order::class, [
                MicroMapperInterface::MAX_DEPTH => 0,
            ])
        );

        return $entity;
    }
}