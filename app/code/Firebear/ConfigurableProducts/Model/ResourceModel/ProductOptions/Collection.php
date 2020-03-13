<?php



namespace Firebear\ConfigurableProducts\Model\ResourceModel\ProductOptions;

use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;

class Collection extends AbstractCollection
{

    protected $_idFieldName = 'item_id';

    /**
     * Define resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init(
            'Firebear\ConfigurableProducts\Model\ProductOptions',
            'Firebear\ConfigurableProducts\Model\ResourceModel\ProductOptions'
        );
    }
}
