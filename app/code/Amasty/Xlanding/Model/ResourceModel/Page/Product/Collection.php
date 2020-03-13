<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2019 Amasty (https://www.amasty.com)
 * @package Amasty_Xlanding
 */


namespace Amasty\Xlanding\Model\ResourceModel\Page\Product;

use Magento\Catalog\Model\Product\Attribute\Source\Status;
use Magento\Catalog\Model\Product\Visibility;

class Collection extends \Magento\Catalog\Model\ResourceModel\Product\Collection
{
    /**
     * @return array
     */
    public function getProductIds()
    {
        $this->_beforeLoad();
        $this->_renderFilters();
        $this->_renderOrders();
        $select = clone $this->getSelect();
        $select->reset(\Magento\Framework\DB\Select::COLUMNS);
        $select->columns('e.entity_id');

        return $this->getConnection()->fetchCol($select);
    }

    /**
     * @return $this
     */
    protected function _beforeLoad()
    {
        $this->addAttributeToFilter('status', Status::STATUS_ENABLED);
        $this->addAttributeToFilter('visibility', [
            'in' => [
                Visibility::VISIBILITY_IN_CATALOG,
                Visibility::VISIBILITY_IN_SEARCH,
                Visibility::VISIBILITY_BOTH
            ]
        ]);
        return parent::_beforeLoad();
    }
}