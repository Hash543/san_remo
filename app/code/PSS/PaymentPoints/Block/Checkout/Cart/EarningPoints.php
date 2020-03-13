<?php
/**
 * @author Israel Yasis
 */
namespace PSS\PaymentPoints\Block\Checkout\Cart;

use PSS\PaymentPoints\Helper\Data as HelperPoints;

class EarningPoints extends \Magento\Checkout\Block\Cart {

    /**
     * @return bool
     */
    public function showEarningPoints() {
        return (boolean)$this->_scopeConfig->getValue(HelperPoints::XML_PATH_CONFIG_SHOW_EARNING_POINTS_CART,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
    }

    /**
     * Get the Earning Points with the current Cart
     * @return int
     */
    public function getEarningPointsCart() {
        $earningPoints = 0;
        $quote = $this->getQuote();
        if($quote && $quote->getId() && $quote->getData('calculate_earning_points') > 0) {
            $earningPoints = $quote->getData('calculate_earning_points');
        }
        return $earningPoints;
    }

    /**
     * @return string
     */
    public function getMessageEarningPoints() {
        $earningPointsMessage = $this->_scopeConfig->getValue(HelperPoints::XML_PATH_CONFIG_MESSAGE_EARNING_POINTS_CART,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
        return sprintf($earningPointsMessage, $this->getEarningPointsCart());
    }
}