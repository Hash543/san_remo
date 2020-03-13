<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2018 Amasty (https://www.amasty.com)
 * @package Amasty_Rules
 */
namespace Amasty\Rules\Helper;

class Discount extends \Magento\Framework\App\Helper\AbstractHelper
{

    public static $maxDiscount;
    /**
     * @var \Magento\Framework\App\Config\scopeConfigInterface
     */
    protected $_scopeConfig;

    /**
     * @var \Magento\Framework\Pricing\PriceCurrencyInterface
     */
    protected $_priceCurrency;

    /**
     * Discount constructor.
     *
     * @param \Magento\Framework\App\Config\scopeConfigInterface $scopeConfig
     * @param \Magento\Framework\Pricing\PriceCurrencyInterface  $priceCurrency
     */
    public function __construct(
        \Magento\Framework\App\Config\scopeConfigInterface $scopeConfig,
        \Magento\Framework\Pricing\PriceCurrencyInterface $priceCurrency
    ) {
        $this->_scopeConfig = $scopeConfig;
        $this->_priceCurrency = $priceCurrency;
    }

    /**
     * @param \Magento\SalesRule\Model\Rule $rule
     * @param \Magento\SalesRule\Model\Rule\Action\Discount\Data $discountData
     * @return \Magento\SalesRule\Model\Rule\Action\Discount\Data
     */
    public function setDiscount(
        \Magento\SalesRule\Model\Rule $rule,
        \Magento\SalesRule\Model\Rule\Action\Discount\Data $discountData
    ) {
        if ($rule->getAmrulesRule()->getMaxDiscount() == 0) {
            return $discountData;
        }

        if (!isset(self::$maxDiscount[$rule->getId()])) {
            self::$maxDiscount[$rule->getId()] = $rule->getAmrulesRule()->getMaxDiscount();
        }

        if (self::$maxDiscount[$rule->getId()] - $discountData->getBaseAmount() < 0) {
            $discountData->setBaseAmount(self::$maxDiscount[$rule->getId()]);
            $discountData->setAmount($this->_priceCurrency->round(self::$maxDiscount[$rule->getId()]));
            $discountData->setBaseOriginalAmount(self::$maxDiscount[$rule->getId()]);
            $discountData->setOriginalAmount($this->_priceCurrency->round(self::$maxDiscount[$rule->getId()]));
            self::$maxDiscount[$rule->getId()] = 0;
        } else {
            self::$maxDiscount[$rule->getId()] = self::$maxDiscount[$rule->getId()] - $discountData->getBaseAmount();
        }

        return $discountData;
    }

}
