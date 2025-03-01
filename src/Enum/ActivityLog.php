<?php

declare(strict_types = 1);

namespace App\Enum;

enum ActivityLog: string
{
    case LOGGED_IN        = 'LOGIN';
    case LOGGED_OUT       = 'LOGOUT';

    case DELETE_CART_ITEM = 'DELETE CART ITEM';
    case UPDATE_CART_ITEM = 'UPDATE CART ITEM';

    case DELETE_CART      = 'DELETE CART';

    case ADD_TO_INVENTORY      = 'ADD TO INVENTORY';
    case GET_INVENTORY_ITEM    = 'GET INVENTORY ITEM';
    case GET_INVENTORY_ITEMS   = 'GET INVENTORY ITEMS';
    case UPDATE_INVENTORY_ITEM = 'UPDATE INVENTORY ITEM';
    case DELETE_INVENTORY_ITEM = 'DELETE INVENTORY ITEM';

    case UPDATE_CART_ITEM_ERROR = 'UPDATE INVENTORY ITEM ERROR';

    /*
     * DiscountApi
     */
    case APPLY_COUPON  = 'APPLY COUPON';
    case REMOVE_COUPON = 'REMOVE COUPON';

    /*
     * CouponApi
     */
    case POST_COUPON   = 'POST COUPON';
    case UPDATE_COUPON = 'UPDATE COUPON';
    case DELETE_COUPON = 'DELETE COUPON';

    /*
     * NotificationApi
     */
    case POST_NOTIFICATION   = 'POST NOTIFICATION';
    case DELETE_NOTIFICATION = 'DELETE NOTIFICATION';

    /*
     * OrderApi
     */
    case PLACE_ORDER = 'PLACE ORDER';

    /*
     * PaymentApi: PaymentProcessor
     */
    case INIT_COUPON_CODE_BASED_PAYMENT           = 'INIT COUPON CODE BASED PAYMENT';
    case RESUME_NON_COUPON_BASED_EXISTING_PAYMENT = 'RESUME NON COUPON BASED PAYMENT';
    case INITIALIZE_TO_PROCESS_PAYMENT            = 'INITIALIZE TO PROCESS PAYMENT';
    case PAYMENT_API_ERROR_EXCEPTION              = 'ApiError exception: Exception during payment processing to create stripe session id';

    /*
     * ProductApi
     */
    case CREATE_PRODUCT       = 'CREATE PRODUCT';
    case UPDATE_PRODUCT_INFO  = 'UPDATE PRODUCT INFORMATION';
    case DELETE_PRODUCT       = 'DELETE PRODUCT';
    case UPDATE_PRODUCT_IMAGE = 'UPDATE PRODUCT IMAGE';

    /*
     * ProductCategory
     */
    case CREATE_PRODUCT_CATEGORY = 'CREATE PRODUCT CATEGORY';
    case UPDATE_PRODUCT_CATEGORY = 'UPDATE PRODUCT CATEGORY';
    case DELETE_PRODUCT_CATEGORY = 'DELETE PRODUCT CATEGORY';

    /*
     * ProductReview
     */
    case CREATE_PRODUCT_REVIEW = 'CREATE PRODUCT REVIEW';
    case UPDATE_PRODUCT_REVIEW = 'UPDATE PRODUCT REVIEW';
    case DELETE_PRODUCT_REVIEW = 'DELETE PRODUCT REVIEW';

    /*
     * ShippingAddressApi
     */
    case CREATE_SHIPPING_ADDRESS = 'CREATE SHIPPING ADDRESS';
    case UPDATE_SHIPPING_ADDRESS = 'UPDATE SHIPPING ADDRESS';
    case DELETE_SHIPPING_ADDRESS = 'DELETE SHIPPING ADDRESS';

    /*
     * ShippingMethodApi
     */
    case CREATE_SHIPPING_METHOD = 'CREATE SHIPPING METHOD';
    case UPDATE_SHIPPING_METHOD = 'UPDATE SHIPPING METHOD';
    case DELETE_SHIPPING_METHOD = 'DELETE SHIPPING METHOD';

    /*
     * UserApi
     */
    case CREATE_USER = 'CREATE USER';
    case UPDATE_USER = 'UPDATE USER';
    case DELETE_USER = 'DELETE USER';

    /*
     * WishlistApi
     */
    case CREATE_WISHLIST = 'CREATE WISHLIST';
    case DELETE_WISHLIST = 'DELETE WISHLIST';

    /*
     * Coupon / Payment
     */
    case COUPON_EXPIRED   = 'COUPON EXPIRED';
    case COUPON_NOT_FOUND = 'COUPON NOT FOUND';

    case STRIPE_API_ERROR = 'STRIPE API ERROR';

    public function getDescription(): string
    {
        return match ($this) {
            self::LOGGED_IN        => 'logged in into the system.',
            self::LOGGED_OUT       => 'logged out from the system',
            self::ADD_TO_INVENTORY => 'added item to inventory.',
            self::DELETE_CART_ITEM => $this->getDeleteCartItemDescription(),
        };
    }

    public function getDeleteCartItemDescription(?int $id = null): string
    {
        if ($id !== null) {
            return sprintf('Remove cart Item (Product id: %s)', $id);
        }

        return 'Remove cart Item';
    }
}