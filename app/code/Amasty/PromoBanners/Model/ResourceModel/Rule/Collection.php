<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2019 Amasty (https://www.amasty.com)
 * @package Amasty_PromoBanners
 */


namespace Amasty\PromoBanners\Model\ResourceModel\Rule;

use Amasty\PromoBanners\Model\Rule;

/**
 * @method Rule[] getItems()
 */
class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{
    /**
     * @var string
     */
    protected $_idFieldName = 'id';

    protected function _construct()
    {
        $this->_init(
            'Amasty\PromoBanners\Model\Rule',
            'Amasty\PromoBanners\Model\ResourceModel\Rule'
        );
    }
}
