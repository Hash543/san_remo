<?php




namespace Firebear\ConfigurableProducts\Model\ResourceModel;

use Magento\Framework\Model\ResourceModel\Db\AbstractDb;

class ProductOptions extends AbstractDb
{
    protected function _construct()
    {
        $this->_init('icp_product_attributes', 'item_id');
    }
}
