<?php

namespace App\DataFixtures;

use App\Entity\ProductCategory;
use App\Factory\ActivityLogFactory;
use App\Factory\ProductCategoryFactory;
use App\Factory\ProductFactory;
use App\Factory\ProductReviewFactory;
use App\Factory\UserFactory;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class AppFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {

        // Create users
        UserFactory::createOne([
            'email' => 'sujal@gmail.com',
            'password' => '123',
            'firstName' => 'Sujal',
            'lastName' => 'Khatiwada',
            'accountActiveStatus' => 1,
            'verificationStatus' => 1,
        ]);
        UserFactory::createMany(10);

        // Create categories and products
        ProductCategoryFactory::createMany(10);
        ProductFactory::createMany(10, function () {
            return [
                'category' => ProductCategoryFactory::random(),
            ];
        });

        // Generate unique reviews
        $users = UserFactory::randomSet(10);    // Fetch 10 unique users
        $products = ProductFactory::randomSet(10); // Fetch 10 unique products

        foreach ($products as $product) {
            $user = array_pop($users); // Assign one unique user to each product
            ProductReviewFactory::createOne([
                'product' => $product,
                'owner' => $user,
            ]);
        }

        $manager->flush();
    }
}
