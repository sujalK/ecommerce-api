<?php

namespace App\DataFixtures;

use App\Factory\UserFactory;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class AppFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        // $product = new Product();
        // $manager->persist($product);
        UserFactory::createOne([
            'email'               => 'sujal@gmail.com',
            'password'            => '123',
            'firstName'           => 'sujal',
            'lastName'            => 'Khatiwada',
            'accountActiveStatus' => 1,
            'verificationStatus'  => 1,
        ]);
        UserFactory::createMany(10);

        $manager->flush();
    }
}
