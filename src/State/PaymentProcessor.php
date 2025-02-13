<?php

declare(strict_types = 1);

namespace App\State;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use App\ApiResource\Order\OrderApi;
use App\Entity\Coupon;
use App\Entity\Order;
use App\Entity\OrderItem;
use App\Entity\User;
use App\Exception\CouponExpiredException;
use App\Exception\CouponNotFoundException;
use App\Repository\CouponRepository;
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
        private readonly CouponRepository $couponRepository,
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

        // Validate coupon and get coupon object (if any). If not valid, no discount is applied.
        $coupon = $this->validateAndGetCoupon($order);

        // Build line items for Stripe Checkout. Discount is applied per order item.
        $lineItems = $this->getLineItems($order, $orderItems, $coupon);

        try {
            // Create the Stripe Checkout session
            $sessionId = $this->createStripeCheckoutSession($lineItems);

            // Set the Stripe session ID on the DTO
            $data->stripeSessionId = $sessionId;
            $data->lineItems       = $lineItems;
            $data->order           = $this->microMapper->map($order, OrderApi::class, [
                MicroMapperInterface::MAX_DEPTH => 1,
            ]);

            // Save or update the Payment entity using the generic processor
            $this->processor->process($data, $operation, $uriVariables, $context);

            // get the payment for the order that is 'pending'
            $payment = $this->paymentRepository->findByOrderId($data->order->id);

            if ( ! empty($order->getCouponCode()) ) {
                // update the sessionId
                $payment->setStripeSessionId($sessionId);
                $this->entityManager->flush();

                return ['stripeSessionId' => $sessionId];
            }

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

//        // Save or update the Payment entity using the generic processor
//        $this->processor->process($data, $operation, $uriVariables, $context);

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

    private function validateAndGetCoupon(Order $order): ?Coupon
    {
        assert($order instanceof Order);
        // $coupon = $this->couponRepository->findValidCoupon($order->getCouponCode(), $order->getOwnedBy());

        $coupon = $this->couponRepository->findOneBy(['code' => $order->getCouponCode()]);

        if ( ! $coupon ) {
            return null;
        }

        if ( ! $coupon->isActive() ) {
            throw new CouponExpiredException();
        }

        $now = new \DateTime();

        // If Coupon is applied before coupon activation date, throw CouponNotFoundException
        if ($now < $coupon->getStartDate()) {
            throw new CouponNotFoundException();
        }

        // Check if the coupon is within the valid date range
        if ($now > $coupon->getEndDate()) {
            throw new CouponExpiredException();
        }

        // If total price of the ordered items is less than the minimum cart value, then apply no discount
        if ($order->getTotalPrice() < $coupon->getMinimumCartValue()) {
            return null;
        }

        return $coupon;
    }


    private function getLineItems(object $order, array $orderItems, ?Coupon $coupon): array
    {
        $lineItems = [];

        foreach ($orderItems as $item) {
            assert($item instanceof OrderItem);

            $product   = $item->getProduct();
            $name      = $product->getName();
            $currency  = $order->getCurrency();
            $unitPrice = (string) $item->getUnitPrice();
            $quantity  = $item->getQuantity();

            // Start with finalUnitPrice equal to the original unit price.
            $finalUnitPrice = $unitPrice;

            if ($coupon !== null && $this->isItemEligibleForDiscount($item, $coupon)) {
                $appliesTo = $coupon->getAppliesTo();
                $isPerUnit = isset($appliesTo['each_quantity']) && $appliesTo['each_quantity'] === true;

                if ($coupon->getDiscountType() === 'fixed') {
                    if ($isPerUnit) {
                        // Fixed discount applied per unit.
                        $discountPerUnit = (string) $coupon->getDiscountValue();
                        $finalUnitPrice = bcsub($unitPrice, $discountPerUnit, 2);
                    } else {
                        // Fixed discount applied on the whole item total.
                        $itemTotal = bcmul($unitPrice, (string) $quantity, 2);
                        $totalAfterDiscount = bcsub($itemTotal, (string) $coupon->getDiscountValue(), 2);
                        // Divide by quantity to get the per-unit price.
                        $finalUnitPrice = bcdiv($totalAfterDiscount, (string) $quantity, 2);
                    }
                } elseif ($coupon->getDiscountType() === 'percentage') {
                    if ($isPerUnit) {
                        // Percentage discount applied per unit.
                        $discountPerUnit = bcmul($unitPrice, bcdiv((string)$coupon->getDiscountValue(), '100', 2), 2);
                        $finalUnitPrice = bcsub($unitPrice, $discountPerUnit, 2);
                    } else {
                        // Percentage discount applied on the entire order item.
                        $itemTotal = bcmul($unitPrice, (string) $quantity, 2);
                        $discountTotal = bcmul($itemTotal, bcdiv((string)$coupon->getDiscountValue(), '100', 2), 2);

                        // Cap the discount if it exceeds the maximum allowed.
                        if (bccomp($discountTotal, (string)$coupon->getMaxDiscountAmountForPercentage(), 2) > 0) {
                            $discountTotal = (string)$coupon->getMaxDiscountAmountForPercentage();
                        }
                        $totalAfterDiscount = bcsub($itemTotal, $discountTotal, 2);
                        // Divide by quantity to get the per-unit price.
                        $finalUnitPrice = bcdiv($totalAfterDiscount, (string)$quantity, 2);
                    }
                }
            }

            // Ensure final unit price is not negative.
            if (bccomp($finalUnitPrice, '0.00', 2) < 0) {
                $finalUnitPrice = '0.00';
            }

            // Update the order_item table, unit_price_after_discount ( for tracking purpose)
            $item->setUnitPriceAfterDiscount($finalUnitPrice);

            // Convert the final per-unit price (in dollars) to cents for Stripe.
            $unitAmount = (int) bcmul($finalUnitPrice, '100', 0);

            $lineItems[] = [
                'price_data' => [
                    'currency'     => $currency,
                    'product_data' => [
                        'name' => $name,
                    ],
                    'unit_amount'  => $unitAmount,
                ],
                'quantity' => $quantity,
            ];
        }

        $this->entityManager->flush();

        return $lineItems;
    }


    private function isItemEligibleForDiscount(OrderItem $item, Coupon $coupon): bool
    {
        // Decode the coupon's applies_to JSON data
        $appliesTo = $coupon->getAppliesTo();
        $product   = $item->getProduct();
        $category  = $product->getCategory();

        if (isset($appliesTo['category']) && $appliesTo['category'] == $category->getId()) {
            return true;
        }

        if (isset($appliesTo['product']) && $appliesTo['product'] == $product->getId()) {
            return true;
        }

        return false;
    }

}
