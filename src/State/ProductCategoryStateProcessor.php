<?php

declare(strict_types = 1);

namespace App\State;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\Metadata\Patch;
use ApiPlatform\State\ProcessorInterface;
use App\ApiResource\ProductCategory\ProductCategoryApi;
use App\Exception\DuplicateProductNameException;
use App\Mapper\ProductCategory\ProductCategoryApiToEntityMapper;
use App\Repository\ProductCategoryRepository;

class ProductCategoryStateProcessor implements ProcessorInterface
{

    public function __construct(
        private readonly DtoToEntityStateProcessor $processor,
    )
    {
    }

    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = [])
    {
        assert($data instanceof ProductCategoryApi);

        return $this->processor->process($data, $operation, $uriVariables, $context);
    }
}
