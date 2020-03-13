<?php
/**
 * @author Israel Yasis
 */
namespace PSS\CatalogInventory\Plugin\Model\Stock;

use Magento\Framework\Exception\NoSuchEntityException;
use PSS\Loyalty\Helper\Data as LoyaltyHelper;

class Item {
    /**
     * @var \Magento\Store\Api\StoreRepositoryInterface
     */
    private $storeRepository;
    /**
     * @var \Magento\CatalogInventory\Api\StockConfigurationInterface
     */
    private $stockConfiguration;
    /**
     * Item constructor.
     * @param \Magento\Store\Api\StoreRepositoryInterface $storeRepository
     * @param \Magento\CatalogInventory\Api\StockConfigurationInterface $stockConfiguration
     */
    public function __construct(
        \Magento\Store\Api\StoreRepositoryInterface $storeRepository,
        \Magento\CatalogInventory\Api\StockConfigurationInterface $stockConfiguration
    ) {
        $this->stockConfiguration = $stockConfiguration;
        $this->storeRepository = $storeRepository;
    }

    /**
     * Modify the return of MaxQty because that Store should always be permitted to add max 1
     * @param \Magento\CatalogInventory\Model\Stock\Item $stockItem
     * @param double $maxQty
     * @return double
     */
    public function afterGetMaxSaleQty(\Magento\CatalogInventory\Model\Stock\Item $stockItem, $maxQty) {
        $store = $this->getStore($stockItem->getStoreId());
        if($store->getCode() == LoyaltyHelper::STORE_CODE
            || $store->getCode() == LoyaltyHelper::STOREVIEW_CODE) {
            $maxQty = $this->stockConfiguration->getMaxSaleQty();
        }
        return $maxQty;
    }

    /**
     * @param int $storeId
     * @return \Magento\Store\Model\Store|null
     */
    private function getStore($storeId) {
        try {
            /** @var \Magento\Store\Model\Store $store */
            $store = $this->storeRepository->getById($storeId);
            return $store;
        }catch (NoSuchEntityException $exception) {
            return null;
        }
    }
}