<?php

declare(strict_types = 1);

namespace App\State;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use App\ApiResource\CartItem\CartItemApi;
use App\Contracts\ActivityLogFormatterInterface;
use App\Entity\Cart;
use App\Enum\ActivityLog;
use App\Repository\CartItemRepository;
use App\Service\ActivityLogService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\SecurityBundle\Security;

class DeleteCartItemProcessor implements ProcessorInterface
{

    public function __construct(
        private readonly ActivityLogFormatterInterface $activityLogFormatter,
        private readonly ActivityLogService $activityLogService,
        private readonly DtoToEntityStateProcessor $processor,
        private readonly Security $security,
        private readonly CartItemRepository $cartItemRepository,
        private readonly EntityManagerInterface $entityManager,
    )
    {
    }

    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = [])
    {
        assert($data instanceof CartItemApi);

        // Perform cart item deletion
        $result = $this->processor->process($data, $operation, $uriVariables, $context);

        // log the delete activity
        $this->activityLogService->storeLog(ActivityLog::DELETE_CART_ITEM);

        $this->deleteCartIfNoCartItemIsLeft($data);

        return $result;
    }

    private function deleteCartIfNoCartItemIsLeft(CartItemApi $data): void
    {
        // Fetch the actual Cart entity from the database using its ID
        $cart = $this->entityManager->getRepository(Cart::class)->find($data->cart->id);

        if (!$cart) {
            return; // Cart not found, exit early
        }

        // current logged-in user
        $user = $this->security->getUser();
        $remainingCartItems = $this->cartItemRepository->countRemainingItemsForUserInCart($user, $cart);

        // Check if there is no cart items left
        if ($remainingCartItems === 0) {
            // log activity
            $this->activityLogService->storeLog(ActivityLog::DELETE_CART, $cart);

            // If no items remain, delete the cart as well
            $this->entityManager->remove($cart);
            $this->entityManager->flush();
        }
    }
}
