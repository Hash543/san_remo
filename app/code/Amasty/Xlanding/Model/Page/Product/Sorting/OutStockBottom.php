<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2019 Amasty (https://www.amasty.com)
 * @package Amasty_Xlanding
 */


namespace Amasty\Xlanding\Model\Page\Product\Sorting;

use \Magento\Catalog\Model\ResourceModel\Product\Collection;

class OutStockBottom extends SortAbstract implements SortInterface
{
    /**
     * @return string
     */
    public function getLabel()
    {
        return __("Move out of stock to bottom");
    }

    /**
     * @param Collection $collection
     * @return Collection
     */
    public function sort(Collection $collection)
    {
        if (!$this->moduleManager->isEnabled('Magento_CatalogInventory')) {
            return $collection;
        }
        parent::sort($collection);
        $collection->joinField(
            'is_in_stock',
            'cataloginventory_stock_item',
            'is_in_stock',
            'product_id=entity_id',
            ['stock_id' => $this->getStockId()],
            'left'
        );

        $collection->getSelect()
            ->order('is_in_stock ' . $collection::SORT_ORDER_DESC)
            ->order('e.entity_id ' . $collection::SORT_ORDER_ASC);

        return $collection;
    }
}
