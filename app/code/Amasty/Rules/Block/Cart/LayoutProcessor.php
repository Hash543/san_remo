<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2018 Amasty (https://www.amasty.com)
 * @package Amasty_Rules
 */


namespace Amasty\Rules\Block\Cart;

use Magento\Checkout\Block\Checkout\LayoutProcessorInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use \Magento\Checkout\Model\Session as CheckoutSession;

class LayoutProcessor implements LayoutProcessorInterface
{
    const CART_DISCOUNT_CSS_SELECTOR = '.cart-summary tr[class="totals"]';

    /**
     * @var \Amasty\Rules\Model\DiscountRegistry
     */
    private $discountRegistry;

    /**
     * @var \Amasty\Rules\Model\ConfigModel
     */
    private $configModel;

    public function __construct(
        ScopeConfigInterface $scopeConfig,
        \Amasty\Rules\Model\DiscountRegistry $discountRegistry,
        \Amasty\Rules\Model\ConfigModel $configModel
    ) {
        $this->discountRegistry = $discountRegistry;
        $this->configModel = $configModel;
    }

    /**
     * @param array $jsLayout
     * @return array
     */
    public function process($jsLayout)
    {
        if ($this->configModel->getShowDiscountBreakdown()) {
            $rulesWithDiscount = $this->discountRegistry->getRulesWithAmount();
            $jsLayout['components']['block-totals']['children']['before_grandtotal']['children']['discount-breakdown']
            ['config'] = ['amount' => $rulesWithDiscount, 'selector' => self::CART_DISCOUNT_CSS_SELECTOR];
        }

        return $jsLayout;
    }
}
