<?php

namespace Firebear\ConfigurableProducts\Observer;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\CatalogInventory\Model\ResourceModel\Stock\Status\CollectionFactory as StockStatusCollectionFactory;
use Magento\CatalogInventory\Model\ResourceModel\Stock\StatusFactory as StatusFactory;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Framework\App\RequestInterface;
use Magento\Catalog\Model\ProductRepository;

class ProductSaveAfter implements ObserverInterface
{

    private $stockStatusCollectionFactory;
    private $statusFactory;
    private $storeManagerInterface;
    private $request;
    private $productRepository;

    public function __construct(
        StockStatusCollectionFactory $stockStatusCollectionFactory,
        StatusFactory $statusFactory,
        StoreManagerInterface $storeManagerInterface,
        RequestInterface $request,
        ProductRepository $productRepository
    ) {
        $this->stockStatusCollectionFactory = $stockStatusCollectionFactory;
        $this->statusFactory                = $statusFactory;
        $this->storeManagerInterface        = $storeManagerInterface;
        $this->request                      = $request;
        $this->productRepository            = $productRepository;
    }

    /**
     * @param Observer $observer
     */
    public function execute(Observer $observer)
    {
        $product = $observer->getProduct();
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
                $storeManager = $this->storeManagerInterface;
                $storeId      = (int)$this->request->getParam('store', 0);
                $store        = $storeManager->getStore($storeId);
                $statusModel  = $this->statusFactory->create();
                $statusModel->saveProductStatus($product->getId(), 1, 0, $store->getCode());
            }
        }
    }
}
