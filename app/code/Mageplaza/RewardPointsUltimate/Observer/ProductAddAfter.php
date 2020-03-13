<?php
/**
 * Mageplaza
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the mageplaza.com license that is
 * available through the world-wide-web at this URL:
 * https://www.mageplaza.com/LICENSE.txt
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade this extension to newer
 * version in the future.
 *
 * @category    Mageplaza
 * @package     Mageplaza_RewardPointsUltimate
 * @copyright   Copyright (c) Mageplaza (https://www.mageplaza.com/)
 * @license     https://www.mageplaza.com/LICENSE.txt
 */

namespace Mageplaza\RewardPointsUltimate\Observer;

use Magento\Framework\Event\Observer as EventObserver;
use Magento\Framework\Event\ObserverInterface;
use Mageplaza\RewardPointsUltimate\Helper\Data as HelperData;

/**
 * Class ProductAddAfter
 * @package Mageplaza\RewardPointsUltimate\Observer
 */
class ProductAddAfter implements ObserverInterface
{
    /**
     * @var \Mageplaza\RewardPointsUltimate\Helper\Data
     */
    protected $helperData;

    /**
     * ProductAddAfter constructor.
     *
     * @param HelperData $helperData
     */
    public function __construct(HelperData $helperData)
    {
        $this->helperData = $helperData;
    }

    /**
     * @param \Magento\Framework\Event\Observer $observer
     */
    public function execute(EventObserver $observer)
    {
        if ($this->helperData->isEnabled()) {
            $item = $observer->getEvent()->getData('quote_item');
            $item = $item->getParentItem() ?: $item;
            $product = $item->getProduct()->load($item->getProductId());
            $mpSellProduct = $product->getData('mp_reward_sell_product');
            if ($mpSellProduct > 0) {
                $price = 0;
                $item->setCustomPrice($price);
                $item->setOriginalCustomPrice($price);
                $item->setBaseOriginalPrice($price);
                $item->setMpRewardSellPoints($mpSellProduct);
                $item->getProduct()->setIsSuperMode(true);
            }
        }
    }
}
