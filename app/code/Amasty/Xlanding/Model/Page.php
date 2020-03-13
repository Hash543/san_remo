<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2019 Amasty (https://www.amasty.com)
 * @package Amasty_Xlanding
 */


namespace Amasty\Xlanding\Model;

use Magento\Framework\App\Filesystem\DirectoryList;
use Amasty\Xlanding\Api\Data\PageInterface;
use Magento\Store\Model\Store;

class Page extends \Magento\Cms\Model\Page implements PageInterface
{
    const FILE_PATH_UPLOAD = 'amasty/xlanding/page/';

    /**
     * CMS page cache tag
     */
    const CACHE_TAG = 'amasty_xlanding_page';

    const STATUS_ACTIVE = 1;

    const STATUS_DYNAMIC = 2;

    /**
     * @var string
     */
    protected $_cacheTag = 'amasty_xlanding_page';

    /**
     * Prefix of model events names
     *
     * @var string
     */
    protected $_eventPrefix = 'amasty_xlanding_page';

    /**
     * @var \Amasty\Xlanding\Model\Rule
     */
    private $rule;

    /**
     * @var \Magento\Framework\Filesystem
     */
    private $filesystem;

    /**
     * @var \Magento\MediaStorage\Model\File\UploaderFactory
     */
    private $fileUploaderFactory;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var \Amasty\Base\Model\Serializer
     */
    private $serializer;

    /**
     * @var \Magento\Framework\Indexer\IndexerRegistry
     */
    protected $indexerRegistry;

    /**
     * @var array
     */
    private $productPositionData = [];

    /**
     * @var array
     */
    private $productPositionDataIndex = [];

    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Filesystem $filesystem,
        \Magento\MediaStorage\Model\File\UploaderFactory $fileUploaderFactory,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Amasty\Base\Model\Serializer $serializer,
        \Magento\Framework\Indexer\IndexerRegistry $indexerRegistry,
        \Magento\Framework\Model\ResourceModel\AbstractResource $resource = null,
        \Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,
        array $data = []
    ) {
        $this->filesystem = $filesystem;
        $this->fileUploaderFactory = $fileUploaderFactory;
        $this->storeManager = $storeManager;
        $this->serializer = $serializer;
        $this->indexerRegistry = $indexerRegistry;
        return parent::__construct(
            $context,
            $registry,
            $resource,
            $resourceCollection,
            $data
        );
    }

    protected function _construct()
    {
        $this->_init(\Amasty\Xlanding\Model\ResourceModel\Page::class);
    }

    /**
     * @return \Amasty\Xlanding\Model\Rule
     */
    public function getRule()
    {
        if (!$this->rule) {
            $this->rule = \Magento\Framework\App\ObjectManager::getInstance()
                ->create(\Amasty\Xlanding\Model\Rule::class)->load($this->getId());
        }

        return $this->rule;
    }


    /**
     * @param \Magento\Framework\DB\Select $select
     * @return $this
     */
    public function applyAttributesFilter(\Magento\Framework\DB\Select $select)
    {
        $conditions = $this->getRule()->getConditions();
        if ($conditions instanceof \Amasty\Xlanding\Model\Rule\Condition\Combine) {
            $this->getRule()->setAggregator($conditions->getAggregator());
            $conditions->collectValidatedAttributes($select);
            $condition = $conditions->collectConditionSql();
            if (!empty($condition)) {
                $select->where($condition);
            }
            $select->group('e.entity_id');
        }

        return $this;
    }

    /**
     * @return $this
     */
    public function beforeSave()
    {
        $value = $this->getLayoutFile();

        // if no image was set - nothing to do
        $hasFile = false;

        try {
            $uploader = $this->fileUploaderFactory->create(['fileId' => 'layout_file']);
            $hasFile = true;

        } catch (\Exception $e) {
            if ($e->getCode() != \Magento\MediaStorage\Model\File\Uploader::TMP_NAME_EMPTY) {
                $this->_logger->critical($e);
            }
        }

        if (empty($value) && $hasFile === false) {
            return parent::beforeSave();
        }

        if (!empty($value['delete'])) {
            $this->setData('layout_file', '');

            return parent::beforeSave();
        }

        try {
            $path = $this->filesystem->getDirectoryRead(
                DirectoryList::MEDIA
            )->getAbsolutePath(
                self::FILE_PATH_UPLOAD
            );

            /** @var $uploader \Magento\MediaStorage\Model\File\Uploader */

            $uploader->setAllowedExtensions(['jpg', 'jpeg', 'gif', 'png']);
            $uploader->setAllowRenameFiles(true);
            $result = $uploader->save($path);

            $this->setData('layout_file', $result['file']);
        } catch (\Exception $e) {
            if ($e->getCode() != \Magento\MediaStorage\Model\File\Uploader::TMP_NAME_EMPTY) {
                $this->_logger->critical($e);
            }
        }

        $value = $this->getLayoutFile();

        if (is_array($value)) {
            $this->setData('layout_file', $value['value']);
        }

        return parent::beforeSave();
    }

    /**
     * @return $this
     */
    public function afterSave()
    {
        $result = parent::afterSave();
        $this->_getResource()->saveProductPositionData($this);
        $this->_getResource()->addCommitCallback([$this, 'reindex']);
        return $result;
    }

    /**
     * @return $this
     */
    public function reindex()
    {
        $pageProductIndexer = $this->indexerRegistry->get(Indexer\PageProduct::INDEXER_ID);
        if (!$pageProductIndexer->isScheduled()) {
            $pageProductIndexer->reindexRow($this->getId());
        }

        $catalogSearchIndexer = $this->indexerRegistry->get(\Magento\CatalogSearch\Model\Indexer\Fulltext::INDEXER_ID);
        if (!$catalogSearchIndexer->isScheduled()) {
            $productIds = [];
            foreach ($this->getProductPositionDataIndex() as $positionData) {
                $productIds = array_unique(array_merge($productIds, array_keys($positionData)));
            }
            $catalogSearchIndexer->reindexList($productIds);
        }

        if ($this->isDynamic()) {
            $categoryProductIndexer = $this->indexerRegistry
                ->get(\Magento\Catalog\Model\Indexer\Category\Product::INDEXER_ID);
            if (!$categoryProductIndexer->isScheduled()) {
                $categoryProductIndexer->reindexRow($this->getDynamicCategoryId());
            }
        }
        return $this;
    }

    /**
     * @return string
     */
    public function getLayoutUpdateXml()
    {
        $xml = parent::getLayoutUpdateXml();

        $extra = [
            '<body><attribute name="class" value="amasty-xlanding-columns'
            . $this->getLayoutColumnsCount()
            . '"/></body>'
        ];

        if (!$this->getLayoutIncludeNavigation()) {
            $extra[] = '<body><referenceContainer name="sidebar.main">'
                . '<referenceBlock  name="catalog.leftnav" remove="true"></referenceBlock></referenceContainer></body>';
        }

        return implode('', $extra) . $xml;
    }

    /**
     * @param bool $forCurrentStore
     * @return array
     */
    public function getMetaData($forCurrentStore = false)
    {
        $metaData = $this->serializer->unserialize(parent::getMetaData());

        $result = $metaData;
        if ($forCurrentStore) {
            $result = isset($metaData[$this->storeManager->getStore()->getId()])
                ? $metaData[$this->storeManager->getStore()->getId()]
                : '';
        }

        return $result;
    }

    /**
     * @return int
     */
    public function getPageId()
    {
        return $this->_getData(PageInterface::LANDING_PAGE_ID);
    }

    /**
     * @param int $pageId
     *
     * @return \Amasty\Xlanding\Api\Data\PageInterface
     */
    public function setPageId($pageId)
    {
        $this->setData(PageInterface::LANDING_PAGE_ID, $pageId);

        return $this;
    }

    /**
     * @return string|null
     */
    public function getLayoutColumnsCount()
    {
        return $this->_getData(PageInterface::LAYOUT_COLUMNS_COUNT);
    }

    /**
     * @param string|null $layoutColumnsCount
     *
     * @return \Amasty\Xlanding\Api\Data\PageInterface
     */
    public function setLayoutColumnsCount($layoutColumnsCount)
    {
        $this->setData(PageInterface::LAYOUT_COLUMNS_COUNT, $layoutColumnsCount);

        return $this;
    }

    /**
     * @return int
     */
    public function getLayoutIncludeNavigation()
    {
        return $this->_getData(PageInterface::LAYOUT_INCLUDE_NAVIGATION);
    }

    /**
     * @param int $layoutIncludeNavigation
     *
     * @return \Amasty\Xlanding\Api\Data\PageInterface
     */
    public function setLayoutIncludeNavigation($layoutIncludeNavigation)
    {
        $this->setData(PageInterface::LAYOUT_INCLUDE_NAVIGATION, $layoutIncludeNavigation);

        return $this;
    }

    /**
     * @return string|null
     */
    public function getLayoutHeading()
    {
        return $this->_getData(PageInterface::LAYOUT_HEADING);
    }

    /**
     * @param string|null $layoutHeading
     *
     * @return \Amasty\Xlanding\Api\Data\PageInterface
     */
    public function setLayoutHeading($layoutHeading)
    {
        $this->setData(PageInterface::LAYOUT_HEADING, $layoutHeading);

        return $this;
    }

    /**
     * @return string|null
     */
    public function getLayoutFile()
    {
        return $this->_getData(PageInterface::LAYOUT_FILE);
    }

    /**
     * @param string|null $layoutFile
     *
     * @return \Amasty\Xlanding\Api\Data\PageInterface
     */
    public function setLayoutFile($layoutFile)
    {
        $this->setData(PageInterface::LAYOUT_FILE, $layoutFile);

        return $this;
    }

    /**
     * @return string|null
     */
    public function getLayoutFileAlt()
    {
        return $this->_getData(PageInterface::LAYOUT_FILE_ALT);
    }

    /**
     * @param string|null $layoutFileAlt
     *
     * @return \Amasty\Xlanding\Api\Data\PageInterface
     */
    public function setLayoutFileAlt($layoutFileAlt)
    {
        $this->setData(PageInterface::LAYOUT_FILE_ALT, $layoutFileAlt);

        return $this;
    }

    /**
     * @return string|null
     */
    public function getLayoutTopDescription()
    {
        return $this->_getData(PageInterface::LAYOUT_TOP_DESCRIPTION);
    }

    /**
     * @param string|null $layoutTopDescription
     *
     * @return \Amasty\Xlanding\Api\Data\PageInterface
     */
    public function setLayoutTopDescription($layoutTopDescription)
    {
        $this->setData(PageInterface::LAYOUT_TOP_DESCRIPTION, $layoutTopDescription);

        return $this;
    }

    /**
     * @return string|null
     */
    public function getLayoutBottomDescription()
    {
        return $this->_getData(PageInterface::LAYOUT_BOTTOM_DESCRIPTION);
    }

    /**
     * @param string|null $layoutBottomDescription
     *
     * @return \Amasty\Xlanding\Api\Data\PageInterface
     */
    public function setLayoutBottomDescription($layoutBottomDescription)
    {
        $this->setData(PageInterface::LAYOUT_BOTTOM_DESCRIPTION, $layoutBottomDescription);

        return $this;
    }

    /**
     * @return string|null
     */
    public function getLayoutStaticTop()
    {
        return $this->_getData(PageInterface::LAYOUT_STATIC_TOP);
    }

    /**
     * @param string|null $layoutStaticTop
     *
     * @return \Amasty\Xlanding\Api\Data\PageInterface
     */
    public function setLayoutStaticTop($layoutStaticTop)
    {
        $this->setData(PageInterface::LAYOUT_STATIC_TOP, $layoutStaticTop);

        return $this;
    }

    /**
     * @return string|null
     */
    public function getLayoutStaticBottom()
    {
        return $this->_getData(PageInterface::LAYOUT_STATIC_BOTTOM);
    }

    /**
     * @param string|null $layoutStaticBottom
     *
     * @return \Amasty\Xlanding\Api\Data\PageInterface
     */
    public function setLayoutStaticBottom($layoutStaticBottom)
    {
        $this->setData(PageInterface::LAYOUT_STATIC_BOTTOM, $layoutStaticBottom);

        return $this;
    }

    /**
     * @return string|null
     */
    public function getDefaultSortBy()
    {
        return $this->_getData(PageInterface::DEFAULT_SORT_BY);
    }

    /**
     * @param string|null $defaultSortBy
     *
     * @return \Amasty\Xlanding\Api\Data\PageInterface
     */
    public function setDefaultSortBy($defaultSortBy)
    {
        $this->setData(PageInterface::DEFAULT_SORT_BY, $defaultSortBy);

        return $this;
    }

    /**
     * @return int
     */
    public function getIsActive()
    {
        return (int)$this->_getData(PageInterface::LANDING_IS_ACTIVE);
    }

    /**
     * @return string|null
     */
    public function getConditionsSerialized()
    {
        return $this->_getData(PageInterface::CONDITIONS_SERIALIZED);
    }

    /**
     * @param string|null $conditionsSerialized
     *
     * @return \Amasty\Xlanding\Api\Data\PageInterface
     */
    public function setConditionsSerialized($conditionsSerialized)
    {
        $this->setData(PageInterface::CONDITIONS_SERIALIZED, $conditionsSerialized);

        return $this;
    }

    /**
     * @param string|null $metaData
     *
     * @return \Amasty\Xlanding\Api\Data\PageInterface
     */
    public function setMetaData($metaData)
    {
        $this->setData(PageInterface::META_DATA, $metaData);

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getDynamicCategoryId()
    {
        return $this->getData(self::DYNAMIC_CATEGORY_ID);
    }

    /**
     * @inheritdoc
     */
    public function setDynamicCategoryId($id)
    {
        return $this->setData(self::DYNAMIC_CATEGORY_ID, $id);
    }

    /**
     * @inheritdoc
     */
    public function getAvailableStatuses()
    {
        return [
            self::STATUS_ENABLED => __('Enabled'),
            self::STATUS_DYNAMIC => __('Dynamic Category'),
            self::STATUS_DISABLED => __('Disabled')
        ];
    }

    /**
     * @return bool
     */
    public function isDynamic()
    {
        return $this->getIsActive() == self::STATUS_DYNAMIC;
    }

    /**
     * @param Rule $rule
     * @return $this
     */
    public function setRule( $rule)
    {
        $this->rule = $rule;
        return $this;
    }

    /**
     * @param $positionData
     * @return $this
     */
    public function setProductPositionData($positionData)
    {
        $this->productPositionData = $positionData;
        return $this;
    }

    /**
     * @return array
     */
    public function getProductPositionData($storeId = null)
    {
        if (empty($this->productPositionData)) {
            $positionData = $this->getResource()->getProductPositionData($this, null);
            $this->setProductPositionData($positionData);
        }

        if ($storeId !== null) {
            return isset($this->productPositionData[$storeId]) ?
                $this->productPositionData[$storeId] : [];
        }

        return $this->productPositionData;
    }

    /**
     * @return array
     */
    public function getProductPositionDataIndex($storeId = null)
    {
        if (empty($this->productPositionDataIndex)) {
            $positionData = $this->getResource()->getProductPositionData($this, true);
            $this->productPositionDataIndex = $positionData;
        }

        if ($storeId !== null) {
            return isset($this->productPositionDataIndex[$storeId]) ?
                $this->productPositionDataIndex[$storeId] : [];
        }

        return $this->productPositionDataIndex;
    }
}
