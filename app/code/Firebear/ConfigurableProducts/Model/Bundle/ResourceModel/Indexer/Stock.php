<?php
namespace Firebear\ConfigurableProducts\Model\Bundle\ResourceModel\Indexer;

class Stock extends \Magento\Bundle\Model\ResourceModel\Indexer\Stock
{
    protected function _getStockStatusSelect($entityIds = null, $usePrimaryTable = false)
    {
        $select = parent::_getStockStatusSelect($entityIds, $usePrimaryTable);

        $connection = $this->getConnection();
        $select
            ->joinLeft(
                ['cpr1' => $this->getTable('catalog_product_relation')],
                'cpr1.parent_id = e.entity_id',
                []
            )
            ->joinLeft(
                ['cpsl' => $this->getTable('catalog_product_super_link')],
                'cpsl.parent_id = cpr1.child_id',
                []
            )
            ->joinLeft(
                ['le' => $this->getTable('catalog_product_entity')],
                'le.entity_id = cpsl.product_id',
                []
            )
            ->joinLeft(
                ['i' => $this->getTable('cataloginventory_stock_status')],
                'i.product_id = cpsl.product_id AND cis.website_id = i.website_id AND cis.stock_id = i.stock_id',
                []
            );

        $statusNotNullExpr = $connection->getCheckSql(
            'o.stock_status IS NOT NULL',
            'o.stock_status',
            '0'
        );
        $statusExpr = $connection->getCheckSql(
            'le.required_options = 0',
            'i.stock_status',
            '1'
        );
        $stockExpr = $connection->getCheckSql(
            'cisi.use_config_manage_stock = 0 AND cisi.manage_stock = 0',
            '1',
            'cisi.is_in_stock'
        );
        
        $columnParts = $select->getPart(\Zend_Db_Select::COLUMNS);
        foreach ($columnParts as $i => $column) {
            if (isset($column[2]) && $column[2] == 'status') {
                unset($columnParts[$i]);
                break;
            }
        }
        $select->setPart(\Zend_Db_Select::COLUMNS, $columnParts);

        $greatestSql = $connection->getGreatestSql([
            new \Zend_Db_Expr('MIN(' . $statusNotNullExpr . ')'),
            new \Zend_Db_Expr('MAX(' . $statusExpr . ')'),
        ]);
        $select->columns(
            [
                'status' => $connection->getLeastSql(
                    [
                        $greatestSql,
                        new \Zend_Db_Expr('MIN(' . $stockExpr . ')'),
                    ]
                ),
            ]
        );

        return $select;
    }
}
