<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2019 Amasty (https://www.amasty.com)
 * @package Amasty_Xlanding
 */


namespace Amasty\Xlanding\Model\Page\Product;

use Amasty\Xlanding\Model\Page;
use Magento\Store\Model\Store;

class IndexDataProvider extends \Magento\Framework\Model\AbstractModel
{
    const DEFAULT_PRODUCT = 0;
    const DEFAULT_REQUEST_NAME = 'catalog_view_container';
    const DEFAULT_REQUEST_LIMIT = 0;

    /**
     * @var \Amasty\Xlanding\Model\ResourceModel\Page\Product\CollectionFactory
     */
    private $productCollectionFactory;

    /**
     * @var Sorting
     */
    private $sorting;

    /**
     * @var Page
     */
    private $page;

    /**
     * @var \Magento\Store\Model\App\Emulation
     */
    private $emulation;

    /**
     * @var \Magento\Framework\Search\Request\Config
     */
    private $searchRequestConfig;

    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \Amasty\Xlanding\Model\ResourceModel\Page\Product\CollectionFactory $productCollectionFactory,
        \Amasty\Xlanding\Model\Page\Product\Sorting $sorting,
        \Magento\Store\Model\App\Emulation $emulation,
        \Magento\Framework\Search\Request\Config $searchRequestConfig,
        \Magento\Framework\Model\ResourceModel\AbstractResource $resource = null,
        \Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,
        array $data = []
    ) {
        parent::__construct($context, $registry, $resource, $resourceCollection, $data);
        $this->productCollectionFactory = $productCollectionFactory;
        $this->sorting = $sorting;
        $this->emulation = $emulation;
        $this->searchRequestConfig = $searchRequestConfig;
    }

    /**
     * @param $page
     * @return array
     */
    public function getProductPositionData($page, $storeId)
    {
        $this->setStoreId($storeId);
        $this->emulation->startEnvironmentEmulation(
            $storeId,
            \Magento\Framework\App\Area::AREA_FRONTEND,
            true
        );
        $this->initPage($page);
        $ruleCollection = $this->getRuleCollection();
        $allIds = $ruleCollection->getProductIds();
        $allIds = $this->sortIds($allIds);
        $this->emulation->stopEnvironmentEmulation();
        return array_flip($allIds);
    }

    /**
     * @param $page
     * @param $product
     * @param $storeId
     * @return bool
     */
    public function validateProductByPage($page, $product, $storeId)
    {
        $this->emulation->startEnvironmentEmulation(
            $storeId,
            \Magento\Framework\App\Area::AREA_FRONTEND,
            true
        );
        $validationResult = $page->getRule()->validate($product);
        $this->emulation->stopEnvironmentEmulation();
        return !!$validationResult;
    }

    /**
     * @return \Amasty\Xlanding\Model\ResourceModel\Page\Product\Collection
     */
    private function getRuleCollection()
    {
        $collection = $this->productCollectionFactory->create();
        $page = $this->getPage();
        $page->applyAttributesFilter($collection->getSelect());
        if ($this->isEmptyRule($page->getRule())) {
            $collection->addAttributeToFilter('entity_id', self::DEFAULT_PRODUCT);
        }
        if ($this->getRuleCollectionLimit()) {
            $collection->getSelect()->limit($this->getRuleCollectionLimit());
        }
        $this->setCollectionOrder($collection);
        return $collection;
    }

    /**
     * @param $collection
     * @return $this
     */
    private function setCollectionOrder($collection)
    {
        $this->sorting->applySorting($collection, $this->getPage()->getSortOrder());
        return $this;
    }

    /**
     * @param $ids
     * @return array
     */
    private function sortIds($ids)
    {
        $sorted = $this->preparePositionDataForSort($ids);
        $ids = array_diff($ids, $sorted);
        $itemsCount = count($ids) + count($sorted);
        $idx = 0;
        while ($idx < $itemsCount) {
            if (!isset($sorted[$idx])) {
                $sorted[$idx] = current($ids);
                next($ids);
            }
            $idx++;
        }

        ksort($sorted, SORT_NUMERIC);
        return $sorted;
    }


    /**
     * @param array $ids
     * @return array
     */
    private function preparePositionDataForSort($ids)
    {
        $positionData = array_flip($this->getPage()->getProductPositionData($this->getStoreId()));
        $positionData = array_intersect($positionData, $ids);
        $maxPosition = count($ids) - 1;
        foreach ($positionData as $position => $productId) {
            if ($position > $maxPosition) {
                $positionData[$maxPosition] = $productId;
                $maxPosition--;
            }
        }

        return $positionData;
    }

    /**
     * @param Page $page
     * @throws \Exception
     * @return $this
     */
    private function initPage(Page $page)
    {
        if (!$page->getId()) {
            throw new \Exception(__('Requested page does not exist'));
        }

        if (!$page->getRule()->getId()) {
            throw new \Exception(__('Rule for this page does not exist'));
        }
        $this->page = $page;

        return $this;
    }

    /**
     * @return Page
     */
    public function getPage()
    {
        return $this->page;
    }

    /**
     * @param $storeId
     * @return $this
     */
    public function setStoreId($storeId)
    {
        $this->setData('store_id', $storeId);
        return $this;
    }

    /**
     * @return mixed
     */
    public function getStoreId()
    {
        if (!$this->hasData('store_id')) {
            $this->setData('store_id', Store::DISTRO_STORE_ID);
        }

        return $this->getData('store_id');
    }

    /**
     * @return int
     */
    private function getRuleCollectionLimit()
    {
        $requestData = $this->searchRequestConfig->get(self::DEFAULT_REQUEST_NAME);
        return isset($requestData['size']) ? $requestData['size'] : self::DEFAULT_REQUEST_LIMIT;
    }

    /**
     * @param $rule
     * @return bool
     */
    private function isEmptyRule($rule)
    {
        $sqlConditions = $rule->getConditions()->collectConditionSql();
        return empty($sqlConditions);
    }
}
