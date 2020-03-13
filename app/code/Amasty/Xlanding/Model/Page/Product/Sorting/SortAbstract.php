<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2019 Amasty (https://www.amasty.com)
 * @package Amasty_Xlanding
 */


namespace Amasty\Xlanding\Model\Page\Product\Sorting;

use \Magento\Framework\DB\Select;
use \Magento\Catalog\Model\ResourceModel\Product\Collection;

class SortAbstract
{
    /**
     * @var \Magento\Framework\Module\Manager
     */
    protected $moduleManager;

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * @param \Magento\Framework\Module\Manager $moduleManager
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     */
    public function __construct(
        \Magento\Framework\Module\Manager $moduleManager,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
    ) {
        $this->moduleManager = $moduleManager;
        $this->scopeConfig = $scopeConfig;
    }

    /**
     * @return int
     */
    protected function getStockId()
    {
        return \Magento\CatalogInventory\Model\Stock::DEFAULT_STOCK_ID;
    }

    /**
     * @return string
     */
    protected function ascOrder()
    {
        return Select::SQL_ASC;
    }

    /**
     * @return string
     */
    protected function descOrder()
    {
        return Select::SQL_DESC;
    }

    /**
     * @param Collection $collection
     * @return void
     */
    protected function addPriceData(Collection $collection)
    {
        $connection = $collection->getConnection();
        $select = $collection->getSelect();
        $joinCond = join(
            ' AND ',
            ['price_index.entity_id = e.entity_id']
        );

        $fromPart = $select->getPart(Select::FROM);

        if (!isset($fromPart['price_index'])) {
            $least = $connection->getLeastSql(['price_index.min_price', 'price_index.tier_price']);
            $minimalExpr = $connection->getCheckSql(
                'price_index.tier_price IS NOT NULL',
                $least,
                'price_index.min_price'
            );
            $colls = [
                'price',
                'tax_class_id',
                'final_price',
                'minimal_price' => $minimalExpr,
                'min_price',
                'max_price',
                'tier_price',
            ];
            $tableName = ['price_index' => $collection->getTable('catalog_product_index_price')];
            $select->joinLeft($tableName, $joinCond, $colls);
        } else {
            $fromPart['price_index']['joinCondition'] = $joinCond;
            $select->setPart(Select::FROM, $fromPart);
        }
    }

    public function sort(Collection $collection)
    {
        $collection->getSelect()->reset(Select::ORDER);
        return $collection;
    }
}
