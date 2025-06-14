<?php

declare(strict_types = 1);

namespace Functional\CreateAccount;

use App\Factory\UserFactory;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Zenstruck\Browser\Test\HasBrowser;
use Zenstruck\Foundry\Test\Factories;
use Zenstruck\Foundry\Test\ResetDatabase;
use Zenstruck\Mailer\Test\InteractsWithMailer;
use Zenstruck\Mailer\Test\TestEmail;

class AccountCreationResourceTest extends KernelTestCase
{

    use InteractsWithMailer;
    use HasBrowser;
    use ResetDatabase;
    use Factories;

    public function testPostToCreateAccount(): void
    {

        $this->browser()
             ->post('/api/users', [
                 'headers' => [
                     'Content-Type' => 'application/json',
                     'Accept'       => 'application/json',
                 ],
                 'json' => [
                     'email'     => 'test@test.com',
                     'password'  => 'password',
                     'username'  => 'test',
                     'firstName' => 'sujal',
                     'lastName'  => 'test',
                     'roles'     => ['ROLE_USER']
                 ]
             ])
             ->assertStatus(201)
        ;

        UserFactory::assert()
            ->count(1)
            ->exists(['email' => 'test@test.com']);

        $this->mailer()
             ->assertSentEmailCount(1)
             ->assertEmailSentTo('test@test.com', function (TestEmail $email) {
                 $email
                     ->assertSubject('Account Confirmation')
                     ->assertContains('Welcome to Q-Commerce')
                     ->assertContains('/user/verify')
                     ->assertHasFile('Welcome Message.pdf')
                     // ->dd() // we can chain this dd() to help us see why the test isn't passing in case if something goes wrong during the email test
                 ;
             })
        ;

    }

}