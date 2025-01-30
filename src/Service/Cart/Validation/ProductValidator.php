<?php

declare(strict_types = 1);

namespace App\Service\Cart\Validation;

use App\ApiResource\CartItem\CartItemApi;
use App\Exception\ProductChangeNotAllowedException;

class ProductValidator
{

    /**
     * Compares if existing product is same as new product that we're
     * trying to update/replace with.
     * Returns false if we try to change product during PATCH operation.
     *
     * @param $previousData
     * @param CartItemApi $data
     * @return void
     * @throws ProductChangeNotAllowedException
     */
    public function isValidProduct($previousData, CartItemApi $data): void
    {
        // get the previous data
        assert($previousData instanceof CartItemApi);

        // get previous product id
        $previousProductId = $previousData->product->id;

        // get the new product id
        $newProductId = $data->product->id;

        if ($previousProductId !== $newProductId) {
            throw new ProductChangeNotAllowedException('Product cannot be replaced, please replace quantity only');
        }
    }

}