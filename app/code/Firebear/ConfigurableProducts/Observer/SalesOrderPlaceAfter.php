<?php

namespace Firebear\ConfigurableProducts\Observer;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Catalog\Model\ProductRepository;
use Magento\CatalogInventory\Model\StockRegistry;
use Magento\CatalogInventory\Model\Stock\StockItemRepository;

class SalesOrderPlaceAfter implements ObserverInterface
{
    private $logger;
    private $productRepository;
    private $stockRegistry;
    private $stockItemRepository;

    public function __construct(
        ProductRepository $productRepository,
        StockRegistry $stockRegistry,
        StockItemRepository $stockItemRepository
    ) {
        $this->productRepository   = $productRepository;
        $this->stockRegistry       = $stockRegistry;
        $this->stockItemRepository = $stockItemRepository;
    }

    public function execute(Observer $observer)
    {
        $order                   = $observer->getOrder();
        $items                   = $order->getAllItems();
        $haveProductConfigurable = false;
        $haveProductBundle       = false;
        foreach ($items as $item) {
            if ($item->getProductType() == 'bundle') {
                $haveProductBundle = true;
            }
            if ($item->getProductType() == 'configurable') {
                $haveProductConfigurable = true;
            }
            if ($haveProductConfigurable && $haveProductBundle && !$item->getProduct()->getTypeId() == 'simple' && !$item->getProduct()->getTypeId() == 'virtual') {
                $simpleSku         = $item->getSku();
                $product           = $this->productRepository->get($simpleSku);
                $productId         = $product->getId();
                $productStockData  = $this->stockRegistry->getStockItem($productId);
                $productQtyInStock = $productStockData->getQty();
                $qtyOrdered        = $item->getQtyOrdered();
                if ($productQtyInStock > 0 && $qtyOrdered <= $productQtyInStock) {
                    $stockItem = $this->stockItemRepository->get($productId);
                    $stockItem->setQty($productQtyInStock - $qtyOrdered);
                    $this->stockItemRepository->save($stockItem);
                }
            }
        }

    }
}