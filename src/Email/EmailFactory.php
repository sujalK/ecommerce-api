<?php

declare(strict_types = 1);

namespace App\Email;

use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\Mailer\Header\TagHeader;
use Symfony\Component\Mailer\MailerInterface;

class EmailFactory
{

    public function __construct (
        private readonly MailerInterface $mailer,
    )
    {
    }

    public function createAccountConfirmationEmail(): TemplatedEmail
    {

        return $this->createEmail('account_confirmation')
                    ->htmlTemplate('email/account_confirmation.html.twig')
                    ->subject('Account Confirmation')
            ;
    }

    public function createEmail(string $tag): TemplatedEmail
    {

        $email = new TemplatedEmail();

        $email->getHeaders()->add(new TagHeader($tag));

        return $email;
    }

}