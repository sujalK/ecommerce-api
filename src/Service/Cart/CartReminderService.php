<?php

declare(strict_types = 1);

namespace App\Service\Cart;

use App\Entity\Cart;
use App\Repository\CartRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\Mailer\Header\MetadataHeader;
use Symfony\Component\Mailer\Header\TagHeader;
use Symfony\Component\Mailer\MailerInterface;

class CartReminderService
{

    private const int BATCH_SIZE = 20;

    public function __construct(
        private readonly CartRepository $cartRepository,
        private readonly EntityManagerInterface $em,
        private readonly MailerInterface $mailer,
    ) {
    }

    public function sendReminders(): void
    {
        $i = 0;

        foreach ($this->cartRepository->findCartsToRemind() as $cart) {
            assert($cart instanceof Cart);

            // Send reminder email
            $email = new TemplatedEmail()
                        ->to($cart->getOwner()->getEmail())
                        ->subject('Please continue your shopping')
                        ->htmlTemplate('email/cart_reminder.html.twig')
                        ->context([
                            'cart' => $cart,
                        ])
                    ;

            // set up category( tag ) for the Mailtrap to filter for statistics  related usages.
            $email->getHeaders()->add(new TagHeader('cart_reminder_email'));

            $email->getHeaders()->add(new MetaDataHeader('user_id', (string) $cart->getOwner()->getId()));
            $email->getHeaders()->add(new MetadataHeader('email', $cart->getOwner()->getEmail()));

            $this->mailer->send($email);

            // Mark reminder as sent
            $cart->setReminderSentAt(new \DateTimeImmutable('now', new \DateTimeZone('UTC')));

            $i++;

            if ($i % self::BATCH_SIZE === 0) {
                $this->em->flush();
                $this->em->clear(); // Detach all entities to free memory
            }
        }

        // Flush remaining changes
        $this->em->flush();
        $this->em->clear();
    }

}