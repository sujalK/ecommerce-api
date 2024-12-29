<?php

declare(strict_types = 1);

namespace App\State;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use App\ApiResource\CartItem\CartItemApi;
use App\Contracts\Cart\CartManagerInterface;
use App\Contracts\ErrorHandlerInterface;
use App\Entity\User;
use Symfony\Bundle\SecurityBundle\Security;

class CartItemStateProcessor implements ProcessorInterface
{

    public function __construct (
        private readonly Security $security,
        private readonly CartManagerInterface $cartManager,
        private readonly ErrorHandlerInterface $errorHandler,
    )
    {
    }

    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = [])
    {
        assert($data instanceof CartItemApi);

        $user = $this->security->getUser();
        assert($user instanceof User);

        try {
            $this->cartManager->processCartOperation($user, $data);
        } catch (\Exception $e) {
            return $this->errorHandler->handleCartError($e);
        }

        return $data;
    }
}
