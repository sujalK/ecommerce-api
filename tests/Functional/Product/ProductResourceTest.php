<?php

declare(strict_types = 1);

namespace App\Tests\Functional\Product;

use App\Factory\ProductCategoryFactory;
use App\Factory\ProductFactory;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Zenstruck\Browser\Test\HasBrowser;
use Zenstruck\Foundry\Test\Factories;
use Zenstruck\Foundry\Test\ResetDatabase;

class ProductResourceTest extends KernelTestCase
{

    use HasBrowser;
    use Factories;
    use ResetDatabase;

    public function testPatchToUpdateProduct(): void
    {

        $category = ProductCategoryFactory::createOne([
            'categoryName' => 'Tech',
        ]);

        $product = ProductFactory::createOne([
            'isActive'   => true,
            's3FileName' => 'image_1',
            'category'   => $category
        ]);

        $category2 = ProductCategoryFactory::createOne([
            'categoryName' => 'Fashion',
        ]);

        $this->browser()
             ->patch('/api/products/'. $product->getId(), [
                 'json' => [
                     'name'        => 'changed',
                     'description' => 'changed desc',
                     'price'       => '12.12',
                     'isActive'    => true,
                     'category'    => '/api/categories/'. $category2->getId(),
                 ],
                 'headers' => [
                     'Accept'       => 'application/json',
                     'Content-Type' => 'application/merge-patch+json'
                 ]
             ])
             ->dump()
             ->get('/api/categories/'. $product->getCategory()->getId())
             ->dump()
        ;

    }

}