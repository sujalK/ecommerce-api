<?php

declare(strict_types = 1);

namespace App\Controller;

use App\Contracts\DateAndTimeInterface;
use App\Entity\User;
use App\Exception\InvalidPaymentException;
use App\Repository\OrderRepository;
use App\Repository\PaymentRepository;
use App\Service\SuccessfulPaymentService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

class PaymentController extends AbstractController
{

    public function __construct (
        private readonly SuccessfulPaymentService $paymentService,
        private readonly EntityManagerInterface $entityManager,
        private readonly PaymentRepository $paymentRepository,
        private readonly OrderRepository $orderRepository,
        private readonly Security $security,
    )
    {
    }

    #[Route(path: '/payment', name: 'app_make_payment')]
    public function makePayment(): Response
    {

        return $this->render('payment/pay.html.twig');
    }

    #[Route(path: '/failed_payment', name: 'app_failed_payment')]
    public function failedPayment(): Response
    {

        return $this->render('payment/failed.html.twig');
    }

    #[Route(path: '/success', name: 'app_payment_success')]
    #[IsGranted("ROLE_USER")]
    public function successPayment(Request $request, /* #[CurrentUser] User $user = null */): Response
    {
        // Retrieve the session_id from the query parameters
        $session_id = $request->query->get('session_id');

        // Now you can use $session_id directly in your method
        if ( ! $session_id ) {
            return $this->json(['error' => 'Session ID is required.'], 400);
        }

        $user = $this->security->getUser();
        assert($user instanceof User);

        // Retrieve the user and their most recent pending order
        $order = $this->orderRepository->findMostRecentPendingOrder($user);
        if (!$order) {
            // return $this->json(['error' => 'Oops, Something went wrong', 'invalid' => true], 500);
            return $this->json(['error' => 'No pending orders found for the user.'], 404);
        }

        // fetch the Payment Info for the current order
        $payment = $this->paymentRepository->findOneBy(['stripeSessionId' => $session_id, /* 'order' => $order */]);
        // dd($payment);
        if (!$payment) {
            return $this->json(['error' => 'Payment record not found for the given order.'], 404);
        }

        // If the payment is already marked as 'paid', return success response
        if ($payment->getPaymentStatus() === 'paid') {
            return $this->json(['message' => 'Payment has already been successfully processed.', 'orderId'   => $order->getId(), 'paymentId' => $payment->getId(),]);
        }

        try {
            $this->entityManager->beginTransaction();

            $this->paymentService->processPaymentInfo($session_id, $order, $payment);

            /**
             * 1. Deduct the coupon usage count
             * - Deduct
             */
            // Deduct the coupon usage count


            /**
             * 2. Update the total_price_after_discount in order table for keeping track of total amount after discount
             */
            // Update the total_price_after_discount
            $paymentInfo = $this->paymentService->getPaymentInfoFromStripe($session_id);
            $order->setTotalPriceAfterDiscount((string) $paymentInfo->amount);
            
            // Persist
            $this->entityManager->persist($order);

            $this->entityManager->flush();
            $this->entityManager->commit();
        } catch (InvalidPaymentException) {
            $this->entityManager->rollback();
            return $this->json([
                'error' => 'Payment was not successful. Please try again.'
            ], 500);
        } catch (\Exception $e) {
            $this->entityManager->rollback();
            // Handle exceptions (e.g., invalid session_id)
            return $this->json([
                'message' => 'Payment was not successful. Please try again.'
            ], 500);
        }

        return $this->json([
            'message' => 'Payment was successful.'
        ]);
    }

}