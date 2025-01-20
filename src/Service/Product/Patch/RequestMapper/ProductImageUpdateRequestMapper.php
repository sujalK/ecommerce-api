<?php

declare(strict_types = 1);

namespace App\Service\Product\Patch\RequestMapper;

use App\DataObjects\ProductRequestData;
use App\Exception\FileNotFoundException;
use App\Exception\InventoryException\ProductNotFoundException;
use App\Service\FileValidationService;
use App\Service\Product\Validation\ProductValidationService;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Request;

class ProductImageUpdateRequestMapper
{

    private const string FILE_KEY       = 'file';
    private const string PRODUCT_ID_KEY = 'id';

    public function __construct (
        private readonly FileValidationService $validationService,
        private readonly ProductValidationService $productValidationService,
    )
    {
    }

    public function map(Request $request): ProductRequestData
    {
        $productId    = $this->extractProductId($request);
        $uploadedFile = $this->extractUploadedFile($request);

        $this->validationService->validateFile($uploadedFile);

        return new ProductRequestData($productId, $uploadedFile);
    }

    private function extractProductId(Request $request): string
    {
        $productId = $request->attributes->get(self::PRODUCT_ID_KEY);

        // Check if the product ID is empty or invalid
        if (empty($productId)) {
            throw new ProductNotFoundException('Product ID is missing.');
        }

        if (!is_numeric($productId) || $productId <= 0) {
            throw new ProductNotFoundException('Product ID is invalid.');
        }

        $this->productValidationService->validateProductId($productId);

        return $productId;
    }

    private function extractUploadedFile(Request $request): UploadedFile
    {
        $uploadedFile = $request->files->get(self::FILE_KEY);

        if ( ! $uploadedFile ) {
            throw new FileNotFoundException('No file has been uploaded');
        }

        return $uploadedFile;
    }
}