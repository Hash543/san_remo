<?php

namespace Firebear\ConfigurableProducts\Plugin\Model\Quote\Item;

use Magento\Catalog\Model\ProductRepository;
use Magento\CatalogInventory\Api\Data\StockItemInterface;
use Magento\CatalogInventory\Api\StockRegistryInterface;
use Magento\CatalogInventory\Api\StockStateInterface;
use Magento\CatalogInventory\Helper\Data;
use Magento\CatalogInventory\Model\Quote\Item\QuantityValidator\Initializer\Option;
use Magento\CatalogInventory\Model\Quote\Item\QuantityValidator\Initializer\StockItem;
use Magento\CatalogInventory\Model\Stock;
use Magento\Framework\Event\Observer;
use Magento\Framework\Exception\LocalizedException;

class QuantityValidator extends \Magento\CatalogInventory\Model\Quote\Item\QuantityValidator
{
    private $productRepository;

    public function __construct(
        Option $optionInitializer,
        StockItem $stockItemInitializer,
        StockRegistryInterface $stockRegistry,
        StockStateInterface $stockState,
        ProductRepository $productRepository
    ) {
        $this->productRepository = $productRepository;
        parent::__construct($optionInitializer, $stockItemInitializer, $stockRegistry, $stockState);
    }

    public function aroundValidate(
        \Magento\CatalogInventory\Model\Quote\Item\QuantityValidator $subject,
        callable $proceed,
        \Magento\Framework\Event\Observer $observer
    ) {
        /* @var $quoteItem \Magento\Quote\Model\Quote\Item */
        $quoteItem = $observer->getEvent()->getItem();
        if (!$quoteItem
            || !$quoteItem->getProductId()
            || !$quoteItem->getQuote()
        ) {
            return;
        }
        $product = $quoteItem->getProduct();
        
        if ($product->getTypeId() == 'bundle') {
            $childIds         = $product->getTypeInstance(true)->getChildrenIds($product->getId(), false);
            $haveConfigurable = false;
            foreach ($childIds as $child) {
                foreach ($child as $childProductId) {
                    $childProductModel = $this->productRepository->getById($childProductId);
                    if ($product->getTypeId() == 'bundle' && $childProductModel->getTypeId() == 'configurable') {
                        $haveConfigurable = true;
                    }
                }
            }
            if ($haveConfigurable) {
                $qty = $quoteItem->getQty();

                /* @var \Magento\CatalogInventory\Model\Stock\Item $stockItem */
                $stockItem = $this->stockRegistry->getStockItem(
                    $product->getId(),
                    $product->getStore()->getWebsiteId()
                );
                if (!$stockItem instanceof \Magento\CatalogInventory\Api\Data\StockItemInterface) {
                    throw new \Magento\Framework\Exception\LocalizedException(
                        __('The stock item for Product is not valid.')
                    );
                }

                if (($options = $quoteItem->getQtyOptions()) && $qty > 0) {
                    foreach ($options as $option) {
                        if ($option->getProduct()->getTypeId() != 'configurable') {
                            $this->optionInitializer->initialize($option, $quoteItem, $qty);
                        }
                    }
                } else {
                    $this->stockItemInitializer->initialize($stockItem, $quoteItem, $qty);
                }

                if ($quoteItem->getQuote()->getIsSuperMode()) {
                    return;
                }
                /* @var \Magento\CatalogInventory\Api\Data\StockStatusInterface $stockStatus */
                $stockStatus = $this->stockRegistry->getStockStatus(
                    $product->getId(),
                    $product->getStore()->getWebsiteId()
                );
                /* @var \Magento\CatalogInventory\Api\Data\StockStatusInterface $parentStockStatus */
                $parentStockStatus = false;

                /**
                 * Check if product in stock. For composite products check base (parent) item stock status
                 */
                if ($quoteItem->getParentItem()) {
                    $product           = $quoteItem->getParentItem()->getProduct();
                    $parentStockStatus = $this->stockRegistry->getStockStatus(
                        $product->getId(),
                        $product->getStore()->getWebsiteId()
                    );
                }
                $stockStatus->setStockStatus(1);
                if ($stockStatus) {
                    if ($stockStatus->getStockStatus() === \Magento\CatalogInventory\Model\Stock::STOCK_OUT_OF_STOCK
                        || $parentStockStatus
                        && $parentStockStatus->getStockStatus()
                        == \Magento\CatalogInventory\Model\Stock::STOCK_OUT_OF_STOCK
                    ) {
                        return;
                    } else {
                        // Delete error from item and its quote, if it was set due to item out of stock
                        $this->_removeErrorsFromQuoteAndItem(
                            $quoteItem,
                            \Magento\CatalogInventory\Helper\Data::ERROR_QTY
                        );
                    }
                }
            } else {
                return $proceed($observer);
            }
        } else {
            return $proceed($observer);
        }
    }
}
