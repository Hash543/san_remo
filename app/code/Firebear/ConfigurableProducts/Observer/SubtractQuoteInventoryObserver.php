<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Firebear\ConfigurableProducts\Observer;

use Magento\Framework\Event\ObserverInterface;
use Magento\CatalogInventory\Api\StockManagementInterface;
use Magento\Framework\Event\Observer as EventObserver;
use Psr\Log\LoggerInterface;

/**
 * Catalog inventory module observer
 */
class SubtractQuoteInventoryObserver implements ObserverInterface
{
    /**
     * @var StockManagementInterface
     */
    protected $stockManagement;

    /**
     * @var ProductQty
     */
    protected $productQty;

    /**
     * @var \Magento\CatalogInventory\Observer\ItemsForReindex
     */
    protected $itemsForReindex;

    private $logger;

    private $stockItemRepository;

    /**
     * SubtractQuoteInventoryObserver constructor.
     *
     * @param StockManagementInterface $stockManagement
     * @param ProductQty               $productQty
     * @param ItemsForReindex          $itemsForReindex
     */
    public function __construct(
        StockManagementInterface $stockManagement,
        \Magento\CatalogInventory\Observer\ProductQty $productQty,
        \Magento\CatalogInventory\Observer\ItemsForReindex $itemsForReindex,
        LoggerInterface $logger,
        \Magento\CatalogInventory\Model\Stock\StockItemRepository $stockItemRepository
    ) {
        $this->logger              = $logger;
        $this->stockManagement     = $stockManagement;
        $this->productQty          = $productQty;
        $this->itemsForReindex     = $itemsForReindex;
        $this->stockItemRepository = $stockItemRepository;
    }

    /**
     * Subtract quote items qtys from stock items related with quote items products.
     *
     * Used before order placing to make order save/place transaction smaller
     * Also called after every successful order placement to ensure subtraction of inventory
     *
     * @param EventObserver $observer
     *
     * @return $this
     */
    public function execute(EventObserver $observer)
    {
        /** @var \Magento\Quote\Model\Quote $quote */
        $quote = $observer->getEvent()->getQuote();
        // Maybe we've already processed this quote in some event during order placement
        // e.g. call in event 'sales_model_service_quote_submit_before' and later in 'checkout_submit_all_after'
        if ($quote->getInventoryProcessed()) {
            return $this;
        }
        $items                   = $this->productQty->getProductQty($quote->getAllItems());
        $quoteItems              = $quote->getAllItems();
        $configurableProductFlag = false;
        $bundleProductFlag       = false;
        foreach ($quoteItems as $item) {
            if ($item->getProduct()->getTypeId() == 'configurable') {
                $configurableProductFlag = true;
            }
            if ($item->getProduct()->getTypeId() == 'bundle') {
                $bundleProductFlag = true;
            }
            if ($bundleProductFlag && $configurableProductFlag && !$item->getProduct()->getTypeId() == 'simple' && !$item->getProduct()->getTypeId() == 'virtual') {
                $selectedOption                      = $item->getOptionByCode('simple_product');
                $selectedProduct                     = $selectedOption->getProduct();
                $stockStatus                         = $this->stockItemRepository->get($selectedProduct->getId());
                $stockQtySimpleProductInConfigurable = $stockStatus->getQty();
                if ($item->getQty() > $stockQtySimpleProductInConfigurable) {
                    throw new \Magento\Framework\Exception\LocalizedException(
                        __('Not all of your products are available in the requested quantity.')
                    );
                }
            }
        }


        $itemsForReindex = $this->stockManagement->registerProductsSale(
            $items,
            $quote->getStore()->getWebsiteId()
        );
        $this->itemsForReindex->setItems($itemsForReindex);

        $quote->setInventoryProcessed(true);

        return $this;
    }
}