<?php
/**
 * @author Israel Yasis
 */
namespace PSS\AddStepToCheckout\Block\Checkout\Cart;

class Coupon extends \Magento\Checkout\Block\Cart {
    /**
     * Show the coupon in the cart
     */
    const CONFIG_XML_ENABLE_COUPON_CART = 'checkout/cart/show_coupon';


    /**
     * Check whether show the coupon form or not
     * @return boolean
     */
    public function showCouponForm() {
        return (boolean)$this->_scopeConfig->getValue(self::CONFIG_XML_ENABLE_COUPON_CART,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
    }
}