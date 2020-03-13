<?php
/**
 * @author Cristian Sanclemente <csanclemente@alfa9.com>
 * @copyright Copyright (c) 2017 Alfa9 (http://www.alfa9.com)
 * @package Alfa9
 */

namespace PSS\CRM\Model\ResourceModel\Queue;

class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{
    protected function _construct()
    {
        $this->_init('PSS\CRM\Model\Queue','PSS\CRM\Model\ResourceModel\Queue');
    }
}
