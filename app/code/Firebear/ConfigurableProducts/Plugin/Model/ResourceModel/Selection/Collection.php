<?php

namespace Firebear\ConfigurableProducts\Plugin\Model\ResourceModel\Selection;

class Collection
{
    public function aroundAddQuantityFilter(
        \Magento\Bundle\Model\ResourceModel\Selection\Collection $subject,
        callable $proceed
    ) {
        $stockItemTable   = $subject->getTable('cataloginventory_stock_item');
        $stockStatusTable = $subject->getTable('cataloginventory_stock_status');
        $subject->getSelect()
            ->joinInner(
                ['stock' => $stockStatusTable],
                'selection.product_id = stock.product_id',
                []
            )->joinInner(
                ['stock_item' => $stockItemTable],
                'selection.product_id = stock_item.product_id',
                []
            )
            ->where(
                'stock.stock_status = 1'
            );
        return $subject;
    }
}