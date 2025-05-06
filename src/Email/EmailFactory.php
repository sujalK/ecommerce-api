<?php

declare(strict_types = 1);

namespace App\Email;

use App\ApiResource\User\UserApi;
use App\Entity\Cart;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\Mailer\Header\MetadataHeader;
use Symfony\Component\Mailer\Header\TagHeader;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Address;

class EmailFactory
{

    public function __construct (
        private readonly MailerInterface $mailer,
        #[Autowire('%kernel.project_dir%/assets/QCommerceWelcomeDocument.pdf')]
        private readonly string $welcomeDocumentPath,
    )
    {
    }

    public function createAccountConfirmationEmail(UserApi $user): TemplatedEmail
    {

        return $this->createEmail('account_confirmation', $user)
                    ->to(new Address($user->email, $user->firstName . ' '. $user->lastName))
                    ->subject('Account Confirmation')
                    ->attachFromPath($this->welcomeDocumentPath, 'Welcome Message.pdf', 'application/pdf')
                    ->htmlTemplate('email/account_confirmation.html.twig')
                    ->context([
                        'entity' => $user,
                    ])
            ;
    }

    public function createCartReminderEmail(Cart $cart): TemplatedEmail
    {
        $email = $cart->getOwner()->getEmail();

        return $this->createEmail('cart_reminder', $cart)
                    ->to($email)
                    ->subject('Shopping Reminder')
                    ->htmlTemplate('email/cart_reminder.html.twig')
                    ->context([
                        'cart' => $cart,
                    ]);
    }

    public function createEmail(string $tag, object $instance): TemplatedEmail
    {

        $email = new TemplatedEmail();

        $email->getHeaders()->add(new TagHeader($tag));

        if ($instance instanceof UserApi) {
            // instance of UserApi
            $user = $instance;

            $email->getHeaders()->add(new MetadataHeader('user_id', (string) $user->id));
            $email->getHeaders()->add(new MetadataHeader('email', $user->email));
        }

        if ($instance instanceof Cart) {
            $cart = $instance;

            $email->getHeaders()->add(new MetadataHeader('email', $cart->getOwner()->getEmail()));
        }

        return $email;
    }

}