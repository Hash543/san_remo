<?php
namespace PSS\PaymentPoints\Helper;
/**
 * @author Israel Yasis
 */

class Data extends \Magento\Framework\App\Helper\AbstractHelper{

    const ATTRIBUTE_CALCULATE_POINTS = 'calculate_earning_points';
    /**
     * Settings
     */
    const XML_PATH_CONFIG_SHOW_EARNING_POINTS_CART = 'rewardpoints/display/show_cart';
    const XML_PATH_CONFIG_SHOW_CHANGE_POINTS = 'rewardpoints/display/show_change_points';
    const XML_PATH_CONFIG_MESSAGE_EARNING_POINTS_CART = 'rewardpoints/display/message_cart';
    const XML_PATH_CONFIG_SHOW_SLIDER = 'rewardpoints/display/show_slider';

    /**
     * Show Slider in the part of Change Points
     * @return bool
     */
    public function showSlider() {
        return (boolean)$this->scopeConfig->getValue(self::XML_PATH_CONFIG_SHOW_SLIDER,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
    }

    /**
     * Check if the is enable the change points block in the checkout
     * @return bool
     */
    /*public function showChangePoints() {
        $response = (boolean)$this->scopeConfig->getValue(self::XML_PATH_CONFIG_SHOW_CHANGE_POINTS,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
        return $response;
    }*/
}