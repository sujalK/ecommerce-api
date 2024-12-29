<?php

declare(strict_types = 1);

namespace App\Tests\Functional\ActivityLog;

use App\Factory\ActivityLogFactory;
use App\Factory\ApiTokenFactory;
use App\Factory\UserFactory;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Zenstruck\Browser\Test\HasBrowser;
use Zenstruck\Foundry\Test\Factories;
use Zenstruck\Foundry\Test\ResetDatabase;

class ActivityLogResourceTest extends KernelTestCase
{
    use HasBrowser;
    use ResetDatabase;
    use Factories;

    public function testGetCollectionOfActivityLog(): void
    {
        $user = UserFactory::createOne([
            'email' => 'sujal@gmail.com',
            'username' => 'sujal_khatiwada',
            'firstName' => 'sujal',
            'lastName' => 'khatiwada'
        ]);

        $apiToken = ApiTokenFactory::createOne([
            'owner'  => $user,
            // 'scopes' => ['ROLE_ADMIN']
        ]);

        UserFactory::createMany(10);
        ActivityLogFactory::createMany(5, function() {
            return [
                'owner' => UserFactory::random()
            ];
        });

        $this->browser()
             ->get('/api/activity_logs', [
                 'headers' => [
                     'Authorization' => 'Bearer '. $apiToken->getToken(),
                     'Accept'  => 'application/ld+json',
                 ]
             ])
             ->dump()
        ;

    }


}