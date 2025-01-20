<?php

declare(strict_types = 1);

namespace App\Service\Product\Validation;

use App\Entity\Product;
use App\Exception\InvalidProductInfoException;
use App\Exception\InventoryException\ProductNotFoundException;
use App\Repository\ProductRepository;
use App\Utils\InputHelper;
use Symfony\Component\HttpFoundation\Request;

class ProductValidationService
{
    /**
     * @throws InvalidProductInfoException
     */
    public function validate(Request $request): void
    {
        // Perform data validation (sent during the request)
        $errors = $this->validateProductInfo($request);

        if (!empty($errors)) {
            throw new InvalidProductInfoException($errors);
        }
    }

    /**
     * @throws ProductNotFoundException
     */
    public function checkIfProductExists(?Product $product = null): void
    {
        if (!$product) {
            throw new ProductNotFoundException();
        }
    }

    public function validateProductId(string $productId): void
    {
        if (!is_numeric($productId)) {
            throw new \InvalidArgumentException('Invalid product id');
        }
    }

    public function validateProductInfo(Request $request): array
    {
        $errors = [];

        $name        = InputHelper::trimValue($request->get('name'));
        $description = InputHelper::trimValue($request->get('description'));
        $price       = InputHelper::trimValue($request->get('price'));
        $category    = InputHelper::trimValue($request->get('category'));
        $isActive    = InputHelper::trimValue($request->get('isActive'));

        // Validate `name`
        if (empty($name) || strlen($name) > 100) {
            $errors['name'] = 'This field is required and must not exceed 100 characters.';
        }

        // Validate `description`
        if (empty($description)) {
            $errors['description'] = 'This field is required.';
        }

        // Validate `price`
        if (empty($price) || !preg_match('/^\d{1,8}(\.\d{1,2})?$/', $price)) {
            $errors['price'] = 'The price must be a decimal value with up to 10 digits and 2 decimal places.';
        }

        // Validate `category`
        if (empty($category) || !is_numeric($category)) {
            $errors['category'] = 'The category ID must be a valid integer.';
        }

        // Validate `isActive`
        if (!isset($isActive) || !in_array($isActive, [true, false, '1', '0', 1, 0], true)) {
            $errors['isActive'] = 'This field must be a boolean value (true/false, 1/0).';
        }

        return $errors;
    }
}