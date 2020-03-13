<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2019 Amasty (https://www.amasty.com)
 * @package Amasty_Xlanding
 */


namespace Amasty\Xlanding\Model\ResourceModel\Page;

use Zend_Db_Expr;

class Collection extends \Magento\Cms\Model\ResourceModel\Page\Collection
{
    /**
     * @var string
     */
    private $queryText;

    /**
     * @var string
     */
    private $storeId;

    /**
     * @inheritdoc
     */
    protected function _construct()
    {
        $this->_init('Amasty\Xlanding\Model\Page', 'Amasty\Xlanding\Model\ResourceModel\Page');
        $this->_map['fields']['page_id'] = 'main_table.page_id';
        $this->_map['fields']['store'] = 'store_table.store_id';
    }

    /**
     * Perform operations after collection load
     *
     * @return $this
     */
    protected function _afterLoad()
    {
        $result = parent::_afterLoad();
        $this->performAfterLoad('amasty_xlanding_page_store', $this->getIdFieldName());
        $this->_previewFlag = false;

        return $result;
    }

    /**
     * Perform operations before rendering filters
     *
     * @return void
     */
    protected function _renderFiltersBefore()
    {
        if ($this->queryText) {
            $allColumns = $this->getFulltextIndexColumns($this, $this->getMainTable());

            $where = 'where';
            foreach ($allColumns as $key => $indexColumns) {
                if ($key > 0) {
                    $where = 'orWhere';
                }
                $this->getSelect()
                    ->{$where}(
                        'MATCH(' . implode(',', $indexColumns) . ') AGAINST(?)',
                        $this->queryText
                    )->order(
                        new Zend_Db_Expr(
                            $this->getConnection()->quoteInto(
                                'MATCH(' . implode(',', $indexColumns) . ') AGAINST(?)',
                                $this->queryText
                            ) . ' desc'
                        )
                    );
            }
        }

        $this->joinStoreRelationTable('amasty_xlanding_page_store', $this->getIdFieldName());
    }

    /**
     * @param string $query
     * @return $this
     */
    public function addSearchFilter($query)
    {
        $this->queryText = trim($this->queryText . ' ' . $query);

        return $this;
    }

    /**
     * @return int
     */
    public function getStoreId()
    {
        if ($this->storeId === null) {
            $this->setStoreId($this->storeManager->getStore()->getId());
        }

        return $this->storeId;
    }

    /**
     * @param int $storeId
     * @return $this
     */
    public function setStoreId($storeId)
    {
        if ($storeId instanceof \Magento\Store\Model\Store) {
            $storeId = $storeId->getId();
        }
        $this->storeId = (int)$storeId;

        return $this;
    }

    /**
     * @param $collection
     * @param $indexTable
     * @return array
     */
    protected function getFulltextIndexColumns($collection, $indexTable)
    {
        $indexes = $collection->getConnection()->getIndexList($indexTable);
        $columns = array();
        foreach ($indexes as $index) {
            if (strtoupper($index['INDEX_TYPE']) == 'FULLTEXT') {
                $columns[] = $index['COLUMNS_LIST'];
            }
        }

        return $columns;
    }

    /**
     * @return array
     */
    public function getIndexFulltextValues()
    {
        $fulltextValues = [];
        foreach ($this->getItems() as $id => $item) {
            $fulltextString = '';
            $indexColumns = array_merge(...$this->getFulltextIndexColumns($this, $this->getMainTable()));
            foreach ($indexColumns as $indexColumn) {
                if ($item->getData($indexColumn)) {
                    $fulltextString .= ' ' . $item->getData($indexColumn);
                }
            }

            $fulltextValues[$id] = trim($fulltextString);
        }

        return $fulltextValues;
    }
}
