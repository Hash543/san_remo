<?php

namespace Firebear\ConfigurableProducts\Model\Bundle\Model;

use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Framework\Pricing\PriceCurrencyInterface;
use Magento\Framework\Serialize\Serializer\Json;
use Magento\Framework\EntityManager\MetadataPool;
use Magento\Bundle\Model\ResourceModel\Selection\Collection\FilterApplier as SelectionCollectionFilterApplier;
use Magento\Framework\App\ObjectManager;

class Type extends \Magento\Bundle\Model\Product\Type
{
    /**
     * @var MetadataPool
     */
    private $metadataPool;

    /**
     * @var SelectionCollectionFilterApplier
     */
    private $selectionCollectionFilterApplier;

    protected $_scopeConfig = null;

    /**
     * @param \Magento\Catalog\Model\Product\Option                           $catalogProductOption
     * @param \Magento\Eav\Model\Config                                       $eavConfig
     * @param \Magento\Catalog\Model\Product\Type                             $catalogProductType
     * @param \Magento\Framework\Event\ManagerInterface                       $eventManager
     * @param \Magento\MediaStorage\Helper\File\Storage\Database              $fileStorageDb
     * @param \Magento\Framework\Filesystem                                   $filesystem
     * @param \Magento\Framework\Registry                                     $coreRegistry
     * @param \Psr\Log\LoggerInterface                                        $logger
     * @param ProductRepositoryInterface                                      $productRepository
     * @param \Magento\Catalog\Helper\Product                                 $catalogProduct
     * @param \Magento\Catalog\Helper\Data                                    $catalogData
     * @param \Magento\Bundle\Model\SelectionFactory                          $bundleModelSelection
     * @param \Magento\Bundle\Model\ResourceModel\BundleFactory               $bundleFactory
     * @param \Magento\Bundle\Model\ResourceModel\Selection\CollectionFactory $bundleCollection
     * @param \Magento\Catalog\Model\Config                                   $config
     * @param \Magento\Bundle\Model\ResourceModel\Selection                   $bundleSelection
     * @param \Magento\Bundle\Model\OptionFactory                             $bundleOption
     * @param \Magento\Store\Model\StoreManagerInterface                      $storeManager
     * @param PriceCurrencyInterface                                          $priceCurrency
     * @param \Magento\CatalogInventory\Api\StockRegistryInterface            $stockRegistry
     * @param \Magento\CatalogInventory\Api\StockStateInterface               $stockState
     * @param \Magento\Framework\Serialize\Serializer\Json                    $serializer
     * @param MetadataPool|null                                               $metadataPool
     * @param SelectionCollectionFilterApplier|null                           $selectionCollectionFilterApplier
     * @param \Magento\Framework\App\Config\ScopeConfigInterface              $scopeConfig
     *
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        \Magento\Catalog\Model\Product\Option $catalogProductOption,
        \Magento\Eav\Model\Config $eavConfig,
        \Magento\Catalog\Model\Product\Type $catalogProductType,
        \Magento\Framework\Event\ManagerInterface $eventManager,
        \Magento\MediaStorage\Helper\File\Storage\Database $fileStorageDb,
        \Magento\Framework\Filesystem $filesystem,
        \Magento\Framework\Registry $coreRegistry,
        \Psr\Log\LoggerInterface $logger,
        ProductRepositoryInterface $productRepository,
        \Magento\Catalog\Helper\Product $catalogProduct,
        \Magento\Catalog\Helper\Data $catalogData,
        \Magento\Bundle\Model\SelectionFactory $bundleModelSelection,
        \Magento\Bundle\Model\ResourceModel\BundleFactory $bundleFactory,
        \Magento\Bundle\Model\ResourceModel\Selection\CollectionFactory $bundleCollection,
        \Magento\Catalog\Model\Config $config,
        \Magento\Bundle\Model\ResourceModel\Selection $bundleSelection,
        \Magento\Bundle\Model\OptionFactory $bundleOption,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        PriceCurrencyInterface $priceCurrency,
        \Magento\CatalogInventory\Api\StockRegistryInterface $stockRegistry,
        \Magento\CatalogInventory\Api\StockStateInterface $stockState,
        Json $serializer = null,
        MetadataPool $metadataPool = null,
        SelectionCollectionFilterApplier $selectionCollectionFilterApplier = null,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
    ) {
        $this->metadataPool = $metadataPool
            ?: ObjectManager::getInstance()->get(MetadataPool::class);

        $this->selectionCollectionFilterApplier = $selectionCollectionFilterApplier
            ?: ObjectManager::getInstance()->get(SelectionCollectionFilterApplier::class);

        parent::__construct(
            $catalogProductOption,
            $eavConfig,
            $catalogProductType,
            $eventManager,
            $fileStorageDb,
            $filesystem,
            $coreRegistry,
            $logger,
            $productRepository,
            $catalogProduct,
            $catalogData,
            $bundleModelSelection,
            $bundleFactory,
            $bundleCollection,
            $config,
            $bundleSelection,
            $bundleOption,
            $storeManager,
            $priceCurrency,
            $stockRegistry,
            $stockState,
            $serializer,
            $metadataPool
        );

        $this->_scopeConfig                     = $scopeConfig;
    }

    /**
     * Retrieve bundle selections collection based on used options
     *
     * @param array                          $optionIds
     * @param \Magento\Catalog\Model\Product $product
     *
     * @return \Magento\Bundle\Model\ResourceModel\Selection\Collection
     */
    public function getSelectionsCollection($optionIds, $product)
    {
        $storeId = $product->getStoreId();

        $metadata = $this->metadataPool->getMetadata(
            \Magento\Catalog\Api\Data\ProductInterface::class
        );

        $selectionsCollection = $this->_bundleCollection->create()
            ->addAttributeToSelect($this->_config->getProductAttributes())
            ->addAttributeToSelect('tax_class_id')//used for calculation item taxes in Bundle with Dynamic Price
            ->setFlag('product_children', true)
            ->setPositionOrder()
            ->addStoreFilter($this->getStoreFilter($product))
            ->setStoreId($storeId)
            ->setOptionIdsFilter($optionIds);

        $this->selectionCollectionFilterApplier->apply(
            $selectionsCollection,
            'parent_product_id',
            $product->getData($metadata->getLinkField())
        );

        if (!$this->_catalogData->isPriceGlobal() && $storeId) {
            $websiteId = $this->_storeManager->getStore($storeId)
                ->getWebsiteId();
            $selectionsCollection->joinPrices($websiteId);
        }

        return $selectionsCollection;
    }

    /**
     * Retrieve bundle selections collection based on ids
     *
     * @param array                          $selectionIds
     * @param \Magento\Catalog\Model\Product $product
     *
     * @return \Magento\Bundle\Model\ResourceModel\Selection\Collection
     */
    public function getSelectionsByIds($selectionIds, $product)
    {
        sort($selectionIds);

        $usedSelections    = $product->getData($this->_keyUsedSelections);
        $usedSelectionsIds = $product->getData($this->_keyUsedSelectionsIds);

        if (!$usedSelections || $usedSelectionsIds !== $selectionIds) {
            $storeId        = $product->getStoreId();
            $usedSelections = $this->_bundleCollection
                ->create()
                ->addAttributeToSelect('*')
                ->setFlag('product_children', true)
                ->addStoreFilter($this->getStoreFilter($product))
                ->setStoreId($storeId)
                ->setPositionOrder()
                ->setSelectionIdsFilter($selectionIds);

            if (!$this->_catalogData->isPriceGlobal() && $storeId) {
                $websiteId = $this->_storeManager->getStore($storeId)
                    ->getWebsiteId();
                $usedSelections->joinPrices($websiteId);
            }
            $product->setData($this->_keyUsedSelections, $usedSelections);
            $product->setData($this->_keyUsedSelectionsIds, $selectionIds);
        }

        return $usedSelections;
    }

    /**
     * @param \Magento\Framework\DataObject $selection
     * @param int[]                         $qtys
     * @param int                           $selectionOptionId
     *
     * @return float
     */
    protected function getQty($selection, $qtys, $selectionOptionId)
    {
        if ($selection->getSelectionCanChangeQty() && isset($qtys[$selectionOptionId])) {
            if (is_array($qtys[$selectionOptionId])) {
                $qty = (float)$qtys[$selectionOptionId][$selection->getId()];
            } else {
                $qty = (float)$qtys[$selectionOptionId];
            }
        } else {
            $qty = (float)$selection->getSelectionQty();
        }

        return $qty;
    }

    /**
     * Prepare selected options for bundle product
     *
     * @param  \Magento\Catalog\Model\Product $product
     * @param  \Magento\Framework\DataObject  $buyRequest
     *
     * @return array
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function processBuyRequest($product, $buyRequest)
    {
        $option    = $buyRequest->getBundleOption();
        $optionQty = $buyRequest->getBundleOptionQty();

        $option    = is_array($option) ? array_filter($option, 'intval') : [];
        $optionQty = is_array($optionQty) ? array_filter($optionQty, 'intval') : [];

        $options = ['bundle_option' => $option, 'bundle_option_qty' => $optionQty];

        return $options;
    }

    /**
     * Checking if we can sale this bundle
     *
     * @param \Magento\Catalog\Model\Product $product
     *
     * @return bool
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    public function isSalable($product)
    {
        return true;
    }

    /**
     * Initialize product(s) for add to cart process.
     * Advanced version of func to prepare product for cart - processMode can be specified there.
     *
     * @param \Magento\Framework\DataObject  $buyRequest
     * @param \Magento\Catalog\Model\Product $product
     * @param null|string                    $processMode
     *
     * @return array|string
     */
    public function prepareForCartAdvanced(
        \Magento\Framework\DataObject $buyRequest,
        $product,
        $processMode = self::PROCESS_MODE_FULL
    ) {
        $_products = $this->_prepareProduct($buyRequest, $product, $processMode);
        $this->processFileQueue();

        return $_products;
    }

    /**
     * Making sure that when you add a configurable to the cart inside a bundle
     * That the bundle unique key keeps the attributes in mind so that it can be added separately
     * Otherwise configurables with different attributes would end up as duplicates
     */
    protected function _prepareProduct(\Magento\Framework\DataObject $buyRequest, $product, $processMode)
    {
        $this->unsetEmptyItems($buyRequest);

        $result = parent::_prepareProduct($buyRequest, $product, $processMode);

        if (is_array($buyRequest->getData('osa'))) {
            foreach ($result as $item) {
                $uniqueKey = $item->getCustomOption('bundle_identity')->getValue();

                foreach ($buyRequest->getData('osa') as $option => $attributes) {
                    foreach ($attributes as $attribute => $value) {
                        $uniqueKey .= '_' . $option . '_' . $attribute . '_' . $value;
                    }
                }

                $item->addCustomOption('bundle_identity', $uniqueKey);
            }
        }

        return $result;
    }

    /**
     * Unset all empty items
     *
     * @param \Magento\Framework\DataObject $buyRequest
     */
    protected function unsetEmptyItems(\Magento\Framework\DataObject $buyRequest)
    {
        $qtys            = $buyRequest->getBundleOptionQty();
        $optionData      = $buyRequest->getBundleOption();
        $superAttributes = $buyRequest->getSuperAttribute();

        if (is_array($qtys)) {
            foreach ($qtys as $id => $value) {
                if ($value == 0) {
                    unset($qtys[$id]);
                    unset($optionData[$id]);
                    unset($superAttributes[$id]);
                }
            }
        }

        $buyRequest->setBundleOptionQty($qtys);
        $buyRequest->setBundleOption($optionData);
        $buyRequest->setSuperAttribute($superAttributes);
    }
}
