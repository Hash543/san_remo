<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2019 Amasty (https://www.amasty.com)
 * @package Amasty_Xlanding
 */


namespace Amasty\Xlanding\Model\Indexer;

use Amasty\Xlanding\Model\ResourceModel\Page\CollectionFactory;
use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use Amasty\Xlanding\Model\Rule;
use Amasty\Xlanding\Model\Page;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class IndexBuilder
{
    const SECONDS_IN_DAY = 86400;
    const PRODUCT_ID = 'product_id';
    const TABLE_NAME = 'amasty_xlanding_page_product_index';

    /**
     * @var \Magento\Framework\App\ResourceConnection
     */
    private $resource;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var \Amasty\Xlanding\Api\PageRepositoryInterface
     */
    private $pageRepository;

    /**
     * @var \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory
     */
    private $productCollectionFactory;

    /**
     * @var \Psr\Log\LoggerInterface
     */
    private $logger;

    /**
     * @var int
     */
    private $batchCount;

    /**
     * @var CollectionFactory
     */
    private $pageCollectionFactory;

    /**
     * @var Page\Product\IndexDataProvider
     */
    private $indexDataProvider;

    /**
     * @var \Magento\Framework\Indexer\IndexerRegistry
     */
    private $indexerRegistry;

    public function __construct(
        \Magento\Framework\App\ResourceConnection $resource,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Amasty\Xlanding\Api\PageRepositoryInterface $pageRepository,
        \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory $productCollectionFactory,
        \Amasty\Xlanding\Model\ResourceModel\Page\CollectionFactory $pageCollectionFactory,
        \Magento\Framework\Api\SearchCriteriaBuilder $searchCriteriaBuilder,
        \Amasty\Xlanding\Model\Page\Product\IndexDataProvider $indexDataProvider,
        \Psr\Log\LoggerInterface $logger,
        \Magento\Framework\Indexer\IndexerRegistry $indexerRegistry,
        $batchCount = 1000
    ) {
        $this->resource = $resource;
        $this->storeManager = $storeManager;
        $this->pageRepository = $pageRepository;
        $this->productCollectionFactory = $productCollectionFactory;
        $this->pageCollectionFactory = $pageCollectionFactory;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->indexDataProvider = $indexDataProvider;
        $this->logger = $logger;
        $this->indexerRegistry = $indexerRegistry;
        $this->batchCount = $batchCount;
    }

    /**
     * @param array $ids
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function reindexByProductIds(array $ids)
    {
        try {
            $this->cleanByProductIds($ids);
            $products = $this->getProducts($ids, true);
            $collection = $this->pageCollectionFactory->create();
            foreach ($collection as $page) {
                foreach ($products as $product) {
                    $this->applyRule($page, $product);
                }
            }
        } catch (\Exception $e) {
            $this->critical($e);
            throw new \Magento\Framework\Exception\LocalizedException(
                __("Landing Page - Product rule indexing failed. See details in exception log.")
            );
        }
    }

    /**
     * @param array $productIds
     * @return \Magento\Catalog\Model\ResourceModel\Product\Collection
     */
    public function getProducts(array $productIds = [], $loadAttributes = false)
    {
        $collection = $this->productCollectionFactory->create();
        if ($loadAttributes) {
            $collection->addAttributeToSelect('*');
        }

        if (!empty($productIds)) {
            $collection->addIdFilter($productIds);
        }
        return $collection;
    }

    /**
     * @throws \Magento\Framework\Exception\LocalizedException
     * @return void
     */
    public function reindexFull()
    {
        try {
            $this->resource->getConnection()->truncateTable($this->getIndexTable());
            $collection = $this->pageCollectionFactory->create();
            $collection->addFieldToFilter('is_active', 1);
            foreach ($collection as $page) {
                $this->doReindex($page);
            }
        } catch (\Exception $e) {
            $this->critical($e);
            throw new \Magento\Framework\Exception\LocalizedException(
                __("Landing Page - Product indexing failed. See details in exception log.")
            );
        }
    }

    /**
     * @param array $ids
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function reindexByPageIds(array $ids)
    {
        $table = $this->getIndexTable();
        $this->resource->getConnection()->delete(
            $table,
            [$this->resource->getConnection()->quoteInto('page_id IN(?)', $ids)]
        );

        try {
            foreach ($ids as $id) {
                try {
                    $page = $this->pageRepository->getById($id);
                    $this->doReindex($page);
                    $this->executeCatalogSerchIndexProcess($page);
                } catch (NoSuchEntityException $e) {
                    // do nothing
                }
            }
        } catch (\Exception $e) {
            $this->critical($e);
            throw new \Magento\Framework\Exception\LocalizedException(
                __("Landing Page - Product indexing failed. See details in exception log.")
            );
        }

    }

    /**
     * @param Page $page
     * @return $this
     */
    private function executeCatalogSerchIndexProcess(Page $page)
    {
        $catalogSearchIndexer = $this->indexerRegistry->get(\Magento\CatalogSearch\Model\Indexer\Fulltext::INDEXER_ID);
        
        $productIds = [];
        foreach ($page->getProductPositionDataIndex() as $positionData) {
            $productIds = array_unique(array_merge($productIds, array_keys($positionData)));
        }
        $catalogSearchIndexer->reindexList($productIds);

        return $this;
    }

    /**
     * @param Page $page
     */
    private function doReindex(Page $page)
    {
        $rows = [];
        $size = 0;
        foreach ($this->storeManager->getStores() as $store) {
            $pageProductPositions = $this->getProductPositionData($page, $store->getId());
            foreach ($pageProductPositions as $productId => $position) {
                $rows[] = [
                    'page_id'           => $page->getId(),
                    self::PRODUCT_ID    => $productId,
                    'store_id'          => $store->getId(),
                    'position'          => $position
                ];

                $size++;
                if ($size == $this->batchCount) {
                    $this->resource->getConnection()->insertOnDuplicate($this->getIndexTable(), $rows);
                    $rows = [];
                    $size = 0;
                }
            }
        }
        if (!empty($rows)) {
            $this->resource->getConnection()->insertMultiple($this->getIndexTable(), $rows);
        }
    }

    /**
     * @param Page $page
     * @return array
     */
    private function getProductPositionData(Page $page, $storeId)
    {
        return $this->indexDataProvider->getProductPositionData($page, $storeId);
    }

    /**
     * @param Page $page
     * @param ProductInterface $product
     * @throws \Exception
     * @return $this
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    private function applyRule(Page $page, ProductInterface $product)
    {
        $table = $this->getIndexTable();
        $this->resource->getConnection()->delete(
            $table,
            [
                $this->resource->getConnection()->quoteInto('page_id = ?', $page->getId()),
                $this->resource->getConnection()->quoteInto(self::PRODUCT_ID . ' = ?', $product->getId())
            ]
        );

        try {
            foreach ($this->storeManager->getStores() as $store){
                if (!$this->indexDataProvider->validateProductByPage($page, $product, $store->getId())) {
                    continue;
                }
                $positions = $this->getProductPositionData($page, $store->getId());
                if (isset($positions[$product->getId()])) {
                    $rows = [
                        'page_id'           => $page->getId(),
                        self::PRODUCT_ID    => $product->getId(),
                        'store_id'          => $store->getId(),
                        'position'          => $positions[$product->getId()]
                    ];
                    $this->resource->getConnection()->insertMultiple($table, $rows);
                }

            }
        } catch (\Exception $e) {
            throw $e;
        }

        return $this;
    }

    /**
     * @param array $productIds
     * @return void
     */
    private function cleanByProductIds($productIds)
    {
        $query = $this->resource->getConnection()->deleteFromSelect(
            $this->resource->getConnection()
                ->select()
                ->from($this->getIndexTable(), self::PRODUCT_ID)
                ->distinct()
                ->where(self::PRODUCT_ID . ' IN (?)', $productIds),
            $this->getIndexTable()
        );

        $this->resource->getConnection()->query($query);
    }

    /**
     * @return string
     */
    private function getIndexTable()
    {
        return $this->resource->getTableName(self::TABLE_NAME);
    }

    /**
     * @param \Exception $exception
     * @return void
     */
    private function critical(\Exception $exception)
    {
        $this->logger->critical($exception);
    }
}
