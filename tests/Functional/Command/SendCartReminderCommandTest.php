<?php

declare(strict_types = 1);

namespace App\Tests\Functional\Command;

use App\Factory\CartFactory;
use App\Factory\UserFactory;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Zenstruck\Browser\Test\HasBrowser;
use Zenstruck\Console\Test\InteractsWithConsole;
use Zenstruck\Foundry\Test\Factories;
use Zenstruck\Foundry\Test\ResetDatabase;
use Zenstruck\Mailer\Test\InteractsWithMailer;
use Zenstruck\Mailer\Test\TestEmail;

class SendCartReminderCommandTest extends KernelTestCase
{

    use ResetDatabase;
    use HasBrowser;
    use Factories;
    use InteractsWithConsole;
    use InteractsWithMailer;

    public function testNoRemindersSent(): void
    {
        $this->executeConsoleCommand('app:send-cart-reminders')
             ->assertSuccessful()
             ->assertOutputContains('Sent 0 cart reminders');
    }


    public function testRemindersSent(): void
    {

        // A - Arrange
        $user = UserFactory::createOne([
            'firstName' => 'test',
            'lastName'  => 'test',
            'email'     => 'test@test.com',
        ]);

        $cart = CartFactory::createOne([
            'status'         => 'active',
            'owner'          => $user,
            'reminderSentAt' => null,
            'createdAt'      => new \DateTimeImmutable('-24 hours'),
            'totalPrice'     => '100.00'
        ]);

        // make sure initially, the reminder is not sent
        $this->assertNull($cart->getReminderSentAt());

        $this->executeConsoleCommand('app:send-cart-reminders')
             ->assertSuccessful()
             ->assertOutputContains('Sent 1 cart reminders')
        ;

        // Assert
        $this->mailer()
             ->assertSentEmailCount(1)
             ->assertEmailSentTo('test@test.com', function (TestEmail $email) {
                 $email
                     ->assertSubject('Shopping Reminder')
                     ->assertContains('Happy shopping!')
                 ;
             });

        $this->assertNotNull($cart->getReminderSentAt());

    }

}