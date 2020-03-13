<?php
/**
 * @author Israel Yasis
 */
namespace Alfa9\GiftProduct\Helper;

class Data extends \Magento\Framework\App\Helper\AbstractHelper {
    const PRICE_GIFT = 'gift_product/gift/price';
    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $scopeConfigInterface;

    /**
     * Data constructor.
     *
     * @param \Magento\Framework\App\Helper\Context $context
     */
    public function __construct(
        \Magento\Framework\App\Helper\Context $context
    ) {
        $this->scopeConfigInterface = $context->getScopeConfig();
        parent::__construct($context);
    }

    public function getConfig($config = null) {
        if(!$config) {
            return null;
        }
        return $this->scopeConfigInterface->getValue($config);
    }

    /**
     * Get the additional price to convert in gift product
     * @return float
     */
    public function getProductPriceGift() {
        $priceGift = 0.0;
        if ($this->scopeConfigInterface->getValue(self::PRICE_GIFT)) {
            $priceGift = $this->scopeConfigInterface->getValue(self::PRICE_GIFT);
        }
        return $priceGift;
    }
}