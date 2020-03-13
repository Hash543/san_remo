<?php

namespace Firebear\ConfigurableProducts\Plugin\Helper\CatalogInventory;

use Magento\Catalog\Model\ProductRepository;
use Magento\CatalogInventory\Model\ResourceModel\Stock\StatusFactory;
use Magento\CatalogInventory\Model\Spi\StockRegistryProviderInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Model\StoreManagerInterface;

class Stock extends \Magento\CatalogInventory\Helper\Stock
{
    private $productRepository;

    public function __construct(
        StoreManagerInterface $storeManager,
        ScopeConfigInterface $scopeConfig,
        StatusFactory $stockStatusFactory,
        StockRegistryProviderInterface $stockRegistryProvider,
        ProductRepository $productRepository
    ) {
        $this->productRepository = $productRepository;
        parent::__construct($storeManager, $scopeConfig, $stockStatusFactory, $stockRegistryProvider);
    }

    public function aroundAssignStatusToProduct(
        \Magento\CatalogInventory\Helper\Stock $subject,
        callable $proceed,
        \Magento\Catalog\Model\Product $product,
        $status = null
    ) {
        if($product->getTypeId() == 'bundle') {
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
            if (!$haveConfigurable) {
                $proceed($product, $status);
            } else {
                $product->setIsSalable(1);
            }
        } else {
            $proceed($product, $status);
        }
    }
}