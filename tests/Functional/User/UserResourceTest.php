<?php

declare(strict_types = 1);

namespace App\Tests\Functional\User;

use App\Entity\ApiToken;
use App\Factory\ApiTokenFactory;
use App\Factory\UserFactory;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Zenstruck\Browser\Json;
use Zenstruck\Browser\Test\HasBrowser;
use Zenstruck\Foundry\Test\Factories;
use Zenstruck\Foundry\Test\ResetDatabase;

class UserResourceTest extends KernelTestCase
{

    use HasBrowser;
    use ResetDatabase;
    use Factories;

    public function testGetUserInfoForLoggedInUser(): void
    {

        $user = UserFactory::createOne([
            'email'    => 'sujal@gmail.com',
            'password' => '123',
        ]);
        UserFactory::createMany(10);
        $token = ApiTokenFactory::createOne([
            'scopes' => [ ApiToken::SCOPE_USER_FETCH ],
            'owner'  => $user,
        ]);

        $this->browser()
             ->get('/api/users', [
                 'headers' => [
                     'Accept'        => 'application/ld+json',
                     'Authorization' => 'Bearer '. $token->getToken(),
                 ]
             ])
             ->use(function(Json $json) {
                 $data = $json->decoded(); // Decode JSON response into PHP array

                 // Ensure 'member' key exists and contains data
                 $members = $data['member'] ?? [];

                 // Extract emails from each user in 'member'
                 $emails = array_column($members, 'email');

                 // Assert that the expected email is present
                 $this->assertContains('sujal@gmail.com', $emails);
             })
        ;
    }

    public function testGetOneUserReturnsTheLoggedInUser(): void
    {

        $user  = UserFactory::createOne([
            'email' => 'test@gmail.com'
        ]);
        $user2 = UserFactory::createOne();
        $token = ApiTokenFactory::createOne([
            'scopes' => [ ApiToken::SCOPE_USER_FETCH ],
            'owner'  => $user,
        ]);

        $this->browser()
             ->get('/api/users/'. $user->getId(), [
                 'headers' => [
                     'Accept'        => 'application/ld+json',
                     'Authorization' => 'Bearer '. $token->getToken()
                 ]
             ])
             ->assertJsonMatches('email', 'test@gmail.com')
        ;

    }

}