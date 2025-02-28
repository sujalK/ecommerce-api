<?php

declare(strict_types = 1);

namespace App\Service;

use App\Contracts\ActivityLogFormatterInterface;
use App\Enum\ActivityLog;

class ActivityLogFormatter implements ActivityLogFormatterInterface
{

    public function getDescription(ActivityLog $activityLog, ?array $context = []): string
    {
        return match ( $activityLog ) {
            ActivityLog::LOGGED_IN              => 'logged in into the system',
            ActivityLog::LOGGED_OUT             => 'logged out from the system',

            ActivityLog::ADD_TO_INVENTORY       => 'added item to inventory',
            ActivityLog::GET_INVENTORY_ITEM     => 'fetch item from inventory',
            ActivityLog::GET_INVENTORY_ITEMS    => 'fetch items from inventory',
            ActivityLog::UPDATE_INVENTORY_ITEM  => 'update item from inventory',
            ActivityLog::DELETE_INVENTORY_ITEM  => 'delete inventory item',

            ActivityLog::DELETE_CART            => isset($context['id']) ? 'delete cart [ Cart id: '. $context['id'] . ']' : 'delete cart',

            ActivityLog::DELETE_CART_ITEM       => $this->getDeleteCartItemDescription($context['id']),
            ActivityLog::UPDATE_CART_ITEM       => $this->updateCartItemDescription($context['id']),
            ActivityLog::UPDATE_CART_ITEM_ERROR => 'update cart item caused error.',

            ActivityLog::APPLY_COUPON           => 'apply coupon to cart',
            ActivityLog::REMOVE_COUPON          => 'remove coupon from cart',

            ActivityLog::POST_COUPON            => 'create coupon',
            ActivityLog::UPDATE_COUPON          => 'update coupon',
            ActivityLog::DELETE_COUPON          => 'delete coupon',

            ActivityLog::POST_NOTIFICATION      => 'create notification',
            ActivityLog::DELETE_NOTIFICATION    => 'delete notification',

            ActivityLog::PLACE_ORDER            => 'place order',

            ActivityLog::INIT_COUPON_CODE_BASED_PAYMENT           => 'resume coupon based payment',
            ActivityLog::RESUME_NON_COUPON_BASED_EXISTING_PAYMENT => 'resume non-coupon based payment',
            ActivityLog::INITIALIZE_TO_PROCESS_PAYMENT            => 'initialize to process payment',
            ActivityLog::PAYMENT_API_ERROR_EXCEPTION              => 'ApiError Exception: Stripe\'s Exception ( during payment processing at stage of generating stripe session id )',

            ActivityLog::COUPON_EXPIRED   => 'coupon expired',
            ActivityLog::COUPON_NOT_FOUND => 'coupon not found',

            ActivityLog::CREATE_PRODUCT       => 'create product',
            ActivityLog::UPDATE_PRODUCT_INFO  => 'update product information',
            ActivityLog::UPDATE_PRODUCT_IMAGE => 'update product image',

            ActivityLog::CREATE_PRODUCT_CATEGORY => 'create product category',
            ActivityLog::UPDATE_PRODUCT_CATEGORY => 'update product category',
            ActivityLog::DELETE_PRODUCT_CATEGORY => 'delete product category',

            ActivityLog::CREATE_PRODUCT_REVIEW => 'create product review',
            ActivityLog::UPDATE_PRODUCT_REVIEW => 'update product review',
            ActivityLog::DELETE_PRODUCT_REVIEW => 'delete product review',

            ActivityLog::CREATE_USER => 'create user',
            ActivityLog::UPDATE_USER => 'update user',
            ActivityLog::DELETE_USER => 'delete user',

            ActivityLog::CREATE_WISHLIST => 'create wishlist',
            ActivityLog::DELETE_WISHLIST => 'delete wishlist',
        };

    }

    public function getDeleteCartItemDescription(?int $id = null): string
    {
        return $id
                ? "Removed cart item ( Product id: $id )"
                : 'Removed cart item'
            ;
    }

    private function updateCartItemDescription(?int $id = null): string
    {
        return $id
            ? "updated cart item ( Cart Item id: $id )"
            : 'updated cart item';
    }

}