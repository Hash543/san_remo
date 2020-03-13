<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2018 Amasty (https://www.amasty.com)
 * @package Amasty_Rules
 */


namespace Amasty\Rules\Plugin;

use \Magento\Checkout\Model\Session as CheckoutSession;

class LayoutProcessor
{
    const CHECKOUT_DISCOUNT_CSS_SELECTOR = '.totals.discount';

    /**
     * @var \Amasty\Rules\Model\DiscountRegistry
     */
    private $discountRegistry;

    /**
     * @var CheckoutSession
     */
    private $checkoutSession;

    /**
     * @var \Amasty\Rules\Model\ConfigModel
     */
    private $configModel;

    function __construct(
        \Amasty\Rules\Model\DiscountRegistry $discountRegistry,
        CheckoutSession $checkoutSession,
        \Amasty\Rules\Model\ConfigModel $configModel
    ) {
        $this->discountRegistry = $discountRegistry;
        $this->checkoutSession = $checkoutSession;
        $this->configModel = $configModel;
    }

    /**
     * @param \Magento\Checkout\Block\Checkout\LayoutProcessor $subject
     * @param $result
     * @return mixed
     */
    public function afterProcess(
        \Magento\Checkout\Block\Checkout\LayoutProcessor $subject,
        $result
    ) {
        if ($this->configModel->getShowDiscountBreakdown()) {
            $this->discountRegistry->updateQuoteData($this->checkoutSession->getQuote());
            $result['components']['checkout']['children']['sidebar']['children']['summary']['children']['totals']
            ['children']['discount-breakdown']['config'] = [
                'amount' => $this->discountRegistry->getRulesWithAmount(),
                'selector' => self::CHECKOUT_DISCOUNT_CSS_SELECTOR
            ];
        }

        return $result;
    }
}
