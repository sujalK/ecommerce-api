<?php

declare(strict_types = 1);

namespace App\State;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use App\ApiResource\Order\OrderApi;
use App\Entity\User;
use App\Repository\OrderItemRepository;
use App\Repository\OrderRepository;
use App\Repository\PaymentRepository;
use Doctrine\ORM\EntityManagerInterface;
use Stripe\Checkout\Session;
use Stripe\Exception\ApiErrorException;
use Stripe\Stripe;
use Symfony\Bundle\SecurityBundle\Security;
use Symfonycasts\MicroMapper\MicroMapperInterface;

class PaymentProcessor implements ProcessorInterface
{

    public function __construct (
        private readonly OrderItemRepository $orderItemRepository,
        private readonly EntityManagerInterface $entityManager,
        private readonly PaymentRepository $paymentRepository,
        private readonly DtoToEntityStateProcessor $processor,
        private readonly MicroMapperInterface $microMapper,
        private readonly OrderRepository $orderRepository,
        private readonly Security $security,
        public string $stripeSecretKey,
    )
    {
    }

    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = [])
    {
        /** @var User $user Get the logged-in user */
        $user = $this->security->getUser();

        // Fetch the order by the owned_by_id (user ID) and order ID
        $order = isset($user) ? $this->orderRepository->findOneBy (criteria: ['ownedBy' => $user, 'status'  => 'pending'], orderBy:  ['createdAt' => 'DESC']) : null;

        // Check if order is found and its status is "placed"
        if ($order === null || $order->getStatus() !== 'pending') {
            throw new \Exception('Order not found or not placed.');
        }

        // Fetch the associated order items (Make sure order items are linked to the order)
        $orderItems = $this->orderItemRepository->findBy(['order' => $order]);

        // Check if there are order items to process
        if (empty($orderItems)) {
            throw new \Exception('No order items found for this order.');
        }

        // Prepare line items for Stripe Checkout
        $lineItems = $this->getLineItems($order, $orderItems);

        try {
            // Create the Stripe Checkout session
            $sessionId = $this->createStripeCheckoutSession($lineItems);

            // Set the Stripe session ID on the DTO
            $data->stripeSessionId = $sessionId;
            $data->lineItems       = $lineItems;
            $data->order           = $this->microMapper->map($order, OrderApi::class, [
                MicroMapperInterface::MAX_DEPTH => 1,
            ]);

            // get the payment for the order that is 'pending'
            $payment = $this->paymentRepository->findByOrderId($data->order->id);

            // If the payment exists, and order is still pending, then return stripeSessionId so that we can continue payment for the same order
            if ($payment && $payment->getOrder()->getStatus() === 'pending') {

                $existingSessionId = $payment->getStripeSessionId();
                $session = Session::retrieve($existingSessionId);

                if ($session->status !== 'expired' && $session->status !== 'canceled') {
                    // Reuse the existing session
                    return ['stripeSessionId' => $existingSessionId];
                } else {
                    // update the sessionId
                    $payment->setStripeSessionId($sessionId);
                    $this->entityManager->flush();

                    return ['stripeSessionId' => $sessionId];
                }
            }

        } catch (ApiErrorException $e) {
            return ['error' => $e->getMessage()];
        }

        // Save or update the Payment entity using the generic processor
        $this->processor->process($data, $operation, $uriVariables, $context);

        return $data;
    }

    private function createStripeCheckoutSession(array $orderItems): string
    {
        // Set the Stripe secret key
        Stripe::setApiKey($this->stripeSecretKey);

        // Create the Stripe Checkout session
        $checkoutSession = Session::create([
            'payment_method_types' => ['card'],
            'line_items'           => $orderItems,
            'mode'                 => 'payment', // One-time payment
            'success_url'          => 'http://127.0.0.1:8000/success?session_id={CHECKOUT_SESSION_ID}',
            // 'cancel_url' => 'http://localhost:9901/cancel.php',
        ]);

        // Return the session ID
        return $checkoutSession->id;
    }

    private function getLineItems(object $order, array $orderItems): array
    {
//        $lineItems = [];
//        foreach ($orderItems as $item) {
//
//            $name       = $item->getProduct()->getName();
//            $currency   = $order->getCurrency();
//            $unitAmount = (int) ($item->getUnitPrice() * 100);
//            $quantity   = $item->getQuantity();
//
//            $lineItems[] = [
//                'price_data'   => [
//                    'currency' => $currency, // Use the currency from the order
//                    'product_data' => [
//                        'name' => $name, // Assuming you have a related Product entity
//                    ],
//                    'unit_amount' => $unitAmount, // Amount in cents
//                ],
//                'quantity' => $quantity,
//            ];
//        }
//
//        return $lineItems;


//        /** First iterated code ( using bcmath) */
//        /** Line items */
//        $lineItems = [];
//        foreach ($orderItems as $item) {
//            $name           = $item->getProduct()->getName();
//            $currency       = $order->getCurrency();
//            $unitPrice      = (string) $item->getUnitPrice();
//            $discountAmount = (string) $item->getDiscountAmount();
//            $quantity       = $item->getQuantity();
//
//            // Calculate final price after applying discount using BCMath
//            $finalPrice = bcsub($unitPrice, $discountAmount, 2);
//            $finalPrice = max($finalPrice, '0.00'); // Ensure price is never negative
//
//            // Convert to cents (Stripe requires the amount in cents)
//            $unitAmount = bcmul($finalPrice, '100');
//
//            $lineItems[] = [
//                'price_data' => [
//                    'currency'     => $currency,
//                    'product_data' => [
//                        'name' => $name,
//                    ],
//                    'unit_amount'  => (int) $unitAmount, // Convert to integer for Stripe
//                ],
//                'quantity' => $quantity,
//            ];
//        }
//
//        return $lineItems;


        // Second and final iteration
        $lineItems = [];

        foreach ($orderItems as $item) {
            $name           = $item->getProduct()->getName();
            $currency       = $order->getCurrency();
            $unitPrice      = (string) $item->getUnitPrice();
            $discountAmount = $item->getDiscountAmount() !== null ? (string) $item->getDiscountAmount() : '0.00';
            $quantity       = $item->getQuantity();

            // Apply discount only if it's greater than 0
            if (bccomp($discountAmount, '0.00', 2) > 0) {
                $finalPrice = bcsub($unitPrice, $discountAmount, 2);
            } else {
                $finalPrice = $unitPrice;
            }

            // Ensure the final price is not negative
            $finalPrice = max($finalPrice, '0.00');

            // Convert to cents (Stripe requires the amount in cents)
            $unitAmount = bcmul($finalPrice, '100');

            $lineItems[] = [
                'price_data' => [
                    'currency'     => $currency,
                    'product_data' => [
                        'name' => $name,
                    ],
                    'unit_amount'  => (int) $unitAmount, // Convert to integer for Stripe
                ],
                'quantity' => $quantity,
            ];
        }

        return $lineItems;
    }

}
