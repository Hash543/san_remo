<?php

namespace Alfa9\StoreInfo\Model\ResourceModel\Stores;

use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;
use Magento\Framework\Data\Collection\Db\FetchStrategyInterface;
use Magento\Framework\Data\Collection\EntityFactoryInterface;
use Magento\Framework\Event\ManagerInterface;
use Magento\Framework\Model\ResourceModel\Db\AbstractDb;
use Magento\Store\Model\Store;
use Magento\Store\Model\StoreManagerInterface;
use Psr\Log\LoggerInterface;
use Alfa9\StoreInfo\Model\Stores;
use Alfa9\StoreInfo\Model\ResourceModel\Stores as StockistResourceModel;

class Collection extends AbstractCollection
{
    /**
     * @var string
     */
    public $_idFieldName = 'storeinfo_id';
    /**
     * Event prefix
     *
     * @var string
     */
    public $_eventPrefix = 'limesharp_stockists_stores_collection';

    /**
     * Event object
     *
     * @var string
     */
    public $_eventObject = 'stockist_collection';

    /**
     * Store manager
     *
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    public $storeManager;

    /**
     * @var array
     */
    public $_joinedFields = [];

    /**
     * constructor
     *
     * @param EntityFactoryInterface $entityFactory
     * @param LoggerInterface $logger
     * @param FetchStrategyInterface $fetchStrategy
     * @param ManagerInterface $eventManager
     * @param StoreManagerInterface $storeManager
     * @param null $connection
     * @param AbstractDb $resource
     */
    public function __construct(
        EntityFactoryInterface $entityFactory,
        LoggerInterface $logger,
        FetchStrategyInterface $fetchStrategy,
        ManagerInterface $eventManager,
        StoreManagerInterface $storeManager,
        $connection = null,
        AbstractDb $resource = null
    ) {
        $this->storeManager = $storeManager;
        parent::__construct($entityFactory, $logger, $fetchStrategy, $eventManager, $connection, $resource);
    }

    /**
     * Define resource model
     *
     * @return void
     */
    public function _construct()
    {
        $this->_init(Stores::class, StockistResourceModel::class);
        $this->_map['fields']['storeinfo_id'] = 'main_table.storeinfo_id';
    }

    /**
     * after collection load
     *
     * @return $this
     */
    public function _afterLoad()
    {
        return parent::_afterLoad();
    }

    /**
     * after collection load
     *
     * @return $this
     */
    public function addFieldToFilter($field, $condition = null)
    {
        if ($field === 'store_id') {
            return $this->addStoreFilter($condition, false);
        }

        return parent::addFieldToFilter($field, $condition);
    }

    /**
     * Add filter by store
     *
     * @param int|\Magento\Store\Model\Store $store
     * @param bool $withAdmin
     * @return $this
     */
    public function addStoreFilter($store, $withAdmin = true)
    {
        if (!$this->getFlag('store_filter_added')) {
            if ($store instanceof Store) {
                $store = [$store->getId()];
            }

            if (!is_array($store)) {
                $store = [$store];
            }

            if ($withAdmin) {
                $store[] = Store::DEFAULT_STORE_ID;
            }

            $filterParams = [];

            foreach ($store as $storeId) {
                $filterParams[]['finset'] = $storeId;
            }

            $this->addFilter('store_id', $filterParams, 'public');
        }
        return $this;
    }

    /**
     * Join store relation table if there is store filter
     *
     * @return void
     * @SuppressWarnings(PHPMD.Ecg.Sql.SlowQuery)
     */
    public function _renderFiltersBefore()
    {
        parent::_renderFiltersBefore();
    }

    /**
     * Get SQL for get record count.
     * Extra GROUP BY strip added.
     *
     * @return \Magento\Framework\DB\Select
     */
    public function getSelectCountSql()
    {
        $countSelect = parent::getSelectCountSql();
        $countSelect->reset(\Zend_Db_Select::GROUP);
        return $countSelect;
    }

    /**
     * @param $tableName
     * @param $linkField
     */
    public function performAfterLoad($tableName, $linkField)
    {
        $linkedIds = $this->getColumnValues($linkField);
        if (count($linkedIds)) {
            $connection = $this->getConnection();
            $select = $connection->select()->from(['alfa9_storeinfo_store' => $this->getTable($tableName)])
                ->where('alfa9_storeinfo_store.' . $linkField . ' IN (?)', $linkedIds);
            // @codingStandardsIgnoreStart
            $result = $connection->fetchAll($select);
            // @codingStandardsIgnoreEnd
            if ($result) {
                $storesData = [];
                foreach ($result as $storeData) {
                    $storesData[$storeData[$linkField]][] = $storeData['store_id'];
                }

                foreach ($this as $item) {
                    $linkedId = $item->getData($linkField);
                    if (!isset($storesData[$linkedId])) {
                        continue;
                    }
                    $storeIdKey = array_search(Store::DEFAULT_STORE_ID, $storesData[$linkedId], true);
                    if ($storeIdKey !== false) {
                        $stores = $this->storeManager->getStores(false, true);
                        $storeId = current($stores)->getId();
                        $storeCode = key($stores);
                    } else {
                        $storeId = current($storesData[$linkedId]);
                        $storeCode = $this->storeManager->getStore($storeId)->getCode();
                    }
                    $item->setData('store_id', $storesData[$linkedId]);
                }
            }
        }
    }
}
