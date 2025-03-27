<?php

declare(strict_types = 1);

namespace App\Tests\Functional\Notification;

use App\Factory\ApiTokenFactory;
use App\Factory\NotificationFactory;
use App\Factory\UserFactory;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Zenstruck\Browser\Test\HasBrowser;
use Zenstruck\Foundry\Test\Factories;
use Zenstruck\Foundry\Test\ResetDatabase;

class NotificationResourceTest extends KernelTestCase
{

    use ResetDatabase;
    use HasBrowser;
    use Factories;

    public function testOwnerCanPatchNotificationReadStatus(): void
    {

        $user  = UserFactory::createOne();

        // Create token belonging to another user
        $user2 = UserFactory::createOne();
        $token = ApiTokenFactory::createOne([
            'owner'   => $user2,
            'expiresAt' => null
        ]);

        $notification = NotificationFactory::createOne([
            'ownedBy' => $user,
            'message' => 'Hey coder!',
        ]);

        $this->browser()
             ->patch('/api/notifications/'. $notification->getId(), [
                 'headers' => [
                     'Content-Type' => 'application/merge-patch+json',
                     'Authorization' => 'Bearer ' . $token->getToken(),
                 ],
                 'json' => [
                     'isRead' => true
                 ]
             ])
             ->dump()
        ;

    }

}