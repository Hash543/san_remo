<?php

namespace Alfa9\StorePickup\Helper;

use Alfa9\StockExpress\Api\StockRepositoryInterface;
use Alfa9\StoreInfo\Api\StockistRepositoryInterface;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\App\Helper\Context;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;

class Data extends \Magento\Framework\App\Helper\AbstractHelper {
    /**
     * @var StockRepositoryInterface
     */

    protected $stockExpress;

    /**
     * @var StockistRepositoryInterface
     */
    protected $stockistRepository;

    /**
     * @var SearchCriteriaBuilder
     */
    protected $searchCriteria;

    /**
     * @var \Magento\Framework\Registry
     */
    protected $registry;

    /**
     * @var \Magento\Catalog\Api\ProductRepositoryInterface
     */
    protected $productRepository;

    /**
     * Register Values
     */
    CONST REGISTER_KEY = 'stock_products';
    CONST REGISTER_VALUE = 'stock_ids';
    const PREFIX_STOCK_EXPRESS = 'stock_express';
    const PREFIX_STOCK_STANDARD = 'standard';
    /**
     * Data constructor.
     * @param Context $context
     * @param StockRepositoryInterface $stockRepository
     * @param StockistRepositoryInterface $stockistRepository
     * @param SearchCriteriaBuilder $searchCriteriaBuilder
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Catalog\Api\ProductRepositoryInterface $productRepository
     */
    public function __construct(
        Context $context,
        StockRepositoryInterface $stockRepository,
        StockistRepositoryInterface $stockistRepository,
        SearchCriteriaBuilder $searchCriteriaBuilder,
        \Magento\Framework\Registry $registry,
        \Magento\Catalog\Api\ProductRepositoryInterface $productRepository
    ) {
        $this->registry = $registry;
        $this->stockExpress = $stockRepository;
        $this->stockistRepository = $stockistRepository;
        $this->searchCriteria = $searchCriteriaBuilder;
        $this->productRepository = $productRepository;
        parent::__construct($context);
    }

    /**
     * @param $response
     * @return array
     */
    public static function getSrIdFromResponse($response) {
        $sanReIds = [];
        $sanReIds[self::PREFIX_STOCK_EXPRESS] = [];
        $sanReIds[self::PREFIX_STOCK_STANDARD] = [];
        if(!is_array($response)) {
            return $sanReIds;
        }
        foreach ($response as $row) {
            foreach ($row as $storeInfo) {
                if(isset($storeInfo['_value']['c']) && isset($storeInfo['_value']['c']['0']) && isset($storeInfo['_value']['c']['1'])) {
                    if($storeInfo['_value']['c']['1'] == '1') {
                        $sanReIds[self::PREFIX_STOCK_EXPRESS][] = (int)$storeInfo['_value']['c']['0'];
                    } else {
                        $sanReIds[self::PREFIX_STOCK_STANDARD][] = (int)$storeInfo['_value']['c']['0'];
                    }
                }
            }
        }
        return $sanReIds;
    }
    /**
     * Get The Available Stores By Product
     * @param $sku
     * @param int $qty
     * @return \Alfa9\StoreInfo\Api\Data\StockistInterface[]
     */
    public function getStoresByProduct($sku, $qty = 0) {
        $response = $this->stockExpress->getList($sku, $qty);
        $sanReIds = self::getSrIdFromResponse($response);
        $allStoresIds = array_merge($sanReIds[self::PREFIX_STOCK_EXPRESS], $sanReIds[self::PREFIX_STOCK_STANDARD]);
        $filter = $this->searchCriteria
            ->addFilter('id_sr', $allStoresIds, 'in')
            ->addFilter('status', 1, 'eq')->create();
        try {
            $store = $this->stockistRepository->getList($filter);
            $stores = [];
            foreach ($store->getItems() as $item) {
                if(in_array($item->getIdSr(), $sanReIds[self::PREFIX_STOCK_EXPRESS])) {
                    $item->setAvailability(__('2Hrs'));
                } else {
                    $item->setAvailability( __('72Hrs'));
                }
                $stores[] = $item;
            }
            return $stores;
        }catch (LocalizedException $exception) {
            return [];
        }
    }
    /**
     * @param array $quoteItems
     * @return array
     */
    public function getStoreIdsFromStockExpress($quoteItems = []) {
        if(!is_array($quoteItems)) {
            return [];
        }
        $products = [];

        foreach ($quoteItems as $quoteItem) {
            /** @var \Magento\Quote\Model\Quote\Item $quoteItem */
            $product = $quoteItem->getProduct();
            if($product->getTypeId() == 'configurable') {
                continue;
            }
            $products[] = [
                'sku' => $quoteItem->getSku(),
                'qty' => $quoteItem->getQty()
            ];
        }
        $products = array_unique($products, SORT_REGULAR);
        $requestProductsDecode = json_encode($products);
        $registryValue = $this->registry->registry(self::REGISTER_KEY);
        if($registryValue == $requestProductsDecode) {
            $response = $this->registry->registry(self::REGISTER_VALUE);
        } else {
            $response = $this->stockExpress->getListMulti($products);
            $this->registry->unregister(self::REGISTER_KEY);
            $this->registry->unregister(self::REGISTER_VALUE);
            $this->registry->register(self::REGISTER_KEY, $requestProductsDecode);
            $this->registry->register(self::REGISTER_VALUE, $response);
        }
        return self::getSrIdFromResponse($response);
    }

    /**
     * Check if all products have stock express
     * @param array $quoteItems
     * @return boolean
     */
    public function isPackageExpress($quoteItems = []) {
        $isPackageExpress = true;
        /** @var \Magento\Quote\Model\Quote\Item $quoteItem */
        foreach ($quoteItems as $quoteItem) {
            $productId = $quoteItem->getProduct()->getId();
            //it seems the file catalog_attributes.xml is not working properly in the server
            /** @var \Magento\Catalog\Model\Product $product  */
            try {
                $product = $this->productRepository->getById($productId);
            }catch (NoSuchEntityException $exception) {
                $product = null;
            }
            if(!$product || $product->getTypeId() == 'configurable') {
                continue;
            }
            /*$writer = new \Zend\Log\Writer\Stream(BP . '/var/log/test.log');
            $logger = new \Zend\Log\Logger();
            $logger->addWriter($writer);
            $logger->info('Added QTY: '.$quoteItem->getQty(). " Stock Express QTY: ".$product->getData('express_stock'));*/
            if($quoteItem->getQty() > $product->getData('express_stock')){
                $isPackageExpress = false;
                break;
            }
            if(!(boolean)$product->getData('express_stock')) {
                $isPackageExpress = false;
                break;
            }
        }
        return $isPackageExpress;
    }
    /**
     * @param array $quoteItems
     * @param string $prefix
     * @return array
     */
    public function getStoreListExpress($quoteItems = [], $prefix = 'reserveandcollect') {

        return $this->getStoreShippingList($quoteItems, $prefix, true);
    }
    /**
     * @param array $quoteItems
     * @param string $prefix
     * @return array
     */
    public function getAllStoreList($quoteItems = [], $prefix = 'storepickup') {
        return $this->getStoreShippingList($quoteItems, $prefix, false);
    }

    /**
     * Get the Store information
     * @param array $quoteItems
     * @param string $prefix
     * @param bool $isExpress
     * @return array
     */
    private function getStoreShippingList($quoteItems = [], $prefix = '', $isExpress = false){
        $sanReIds = $this->getStoreIdsFromStockExpress($quoteItems);

        if($isExpress) {
            $allStoresIds = $sanReIds[self::PREFIX_STOCK_EXPRESS];
        } else {
            $allStoresIds = array_merge($sanReIds[self::PREFIX_STOCK_EXPRESS], $sanReIds[self::PREFIX_STOCK_STANDARD]);
        }
        $allStoresIds = array_values($allStoresIds);
        $filter = $this->searchCriteria->addFilter('id_sr', $allStoresIds,  'in')
            ->addFilter('status', 1, 'eq')->create();
        $storeList = [];
        try {
            $stores = $this->stockistRepository->getList($filter);
            $stores = $stores->getItems();
        } catch(\Exception $e) {
            $stores = [];
        }
        $storesWithFastDelivery = [
            'Barcelona',
            'Hospitalet de Llobregat'
        ];
        /** @var \Alfa9\StoreInfo\Model\Stores $store */
        foreach ($stores as $store) {
            $storeId = $store->getIdSr() ? $store->getIdSr() : 0;
            if($storeId == 29) {
                $holw = '';
            }
            if(in_array($storeId, array_values($sanReIds[self::PREFIX_STOCK_EXPRESS]))) {
                $storeList[] = [
                    'method' => $prefix . $storeId,
                    'city' => $store->getCity(),
                    'region' => $store->getRegion(),
                    'country' => $store->getCountry(),
                    'address' => $store->getAddress() . ', ' . $store->getRegion() . ', ' . $store->getPostcode(),
                    'availability' => __('Disponible en 2h'),
                    'store_id' => $storeId
                ];
            } else {
                $deliveryTime = ( in_array($store->getRegion(),$storesWithFastDelivery) ) ? __("Disponible en 48/72h") : __("Disponible en 5 días");
                $storeList[] = [
                    'method' => $prefix . $storeId,
                    'city' => $store->getCity(),
                    'region' => $store->getRegion(),
                    'country' => $store->getCountry(),
                    'address' => $store->getAddress() . ', ' . $store->getRegion() . ', ' . $store->getPostcode(),
                    'availability' => $deliveryTime,
                    'store_id' => $storeId
                ];
            }
        }
        return $storeList;
    }
}
