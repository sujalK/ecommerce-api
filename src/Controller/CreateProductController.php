<?php

declare(strict_types = 1);

namespace App\Controller;

use App\Service\Product\Post\ProductCreationService;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

class CreateProductController
{
    public function __construct (
        private readonly ProductCreationService $productCreationService,
    ) {
    }
    
    public function __invoke(Request $request): JsonResponse
    {
        return $this->productCreationService->init($request);
    }
}