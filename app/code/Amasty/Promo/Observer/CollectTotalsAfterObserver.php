<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2018 Amasty (https://www.amasty.com)
 * @package Amasty_Promo
 */


namespace Amasty\Promo\Observer;

use Magento\Framework\Event\ObserverInterface;

/**
 * Remove all not allowed items
 */

class CollectTotalsAfterObserver implements ObserverInterface
{
    /**
     * Core registry
     *
     * @var \Magento\Framework\Registry
     */
    private $_coreRegistry;

    /**
     * @var \Amasty\Promo\Helper\Cart
     */
    private $promoCartHelper;

    /**
     * @var \Amasty\Promo\Helper\Item
     */
    private $promoItemHelper;

    /**
     * @var \Amasty\Promo\Model\Registry
     */
    private $promoRegistry;

    public function __construct(
        \Magento\Framework\Registry $registry,
        \Amasty\Promo\Helper\Cart $promoCartHelper,
        \Amasty\Promo\Helper\Item $promoItemHelper,
        \Amasty\Promo\Model\Registry $promoRegistry
    ) {
        $this->_coreRegistry = $registry;
        $this->promoCartHelper = $promoCartHelper;
        $this->promoItemHelper = $promoItemHelper;
        $this->promoRegistry = $promoRegistry;
    }

    /**
     * @param \Magento\Framework\Event\Observer $observer
     * @throws \Exception
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $allowedItems = $this->promoRegistry->getPromoItems();
        $deletedItems = $this->promoRegistry->getDeletedItems();
        $toAdd = $this->_coreRegistry->registry('ampromo_to_add');
        /** @var \Magento\Quote\Model\Quote $quote */
        $quote = $observer->getQuote();

        if (is_array($toAdd)) {
            foreach ($toAdd as $item) {
                if (!array_key_exists($item['product']->getSku(), $deletedItems)) {
                    $this->promoCartHelper->addProduct(
                        $item['product'],
                        $item['qty'],
                        $item['rule_id'],
                        [],
                        $item['discount'],
                        isset($item['discount']) && !empty($item['discount']) ? $item['discount']['minimal_price'] : null,
                        $quote
                    );
                }
            }
        }

        $this->_coreRegistry->unregister('ampromo_to_add');

        foreach ($quote->getAllItems() as $item) {
            if ($this->promoItemHelper->isPromoItem($item)) {
                if ($item->getParentItemId()) {
                    continue;
                }

                $sku = $item->getProduct()->getData('sku');

                $ruleId = $this->promoItemHelper->getRuleId($item);

                if (isset($allowedItems['_groups'][$ruleId])) { // Add one of
                    if ($allowedItems['_groups'][$ruleId]['qty'] <= 0) {
                        $quote->removeItem($item->getId());
                    } elseif ($item->getQty() > $allowedItems['_groups'][$ruleId]['qty']) {
                        $item->setQty($allowedItems['_groups'][$ruleId]['qty']);
                    }

                    $allowedItems['_groups'][$ruleId]['qty'] -= $item->getQty();
                } elseif (isset($allowedItems[$ruleId]['sku'][$sku])) { // Add all of
                    if ($allowedItems[$ruleId]['sku'][$sku]['qty'] <= 0) {
                        $quote->removeItem($item->getId());
                    } elseif ($item->getQty() > $allowedItems[$ruleId]['sku'][$sku]['qty']) {
                        $item->setQty($allowedItems[$ruleId]['sku'][$sku]['qty']);
                    }

                    $allowedItems[$ruleId]['sku'][$sku]['qty'] -= $item->getQty();
                } else {
                    $quote->removeItem($item->getId());
                    $quote->getShippingAddress()->setData('cached_items_all', $quote->getAllItems());
                    $quote->getShippingAddress()->setCollectShippingRates(true);
                    $quote->collectTotals();
                }

            }
        }

        $this->promoCartHelper->updateQuoteTotalQty(
            false,
            $quote
        );
    }
}
