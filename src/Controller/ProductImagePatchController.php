<?php

declare(strict_types = 1);

namespace App\Controller;

use App\Service\Product\Patch\ProductImageUpdateService;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

class ProductImagePatchController
{
    public function __construct (
        private readonly ProductImageUpdateService $productImageUpdateService,
    )
    {
    }

    public function __invoke(Request $request): JsonResponse
    {
        return $this->productImageUpdateService->init($request);
    }

}