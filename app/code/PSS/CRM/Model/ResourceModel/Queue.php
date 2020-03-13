<?php
/**
 * @author Cristian Sanclemente <csanclemente@alfa9.com>
 * @copyright Copyright (c) 2017 Alfa9 (http://www.alfa9.com)
 * @package Alfa9
 */

namespace PSS\CRM\Model\ResourceModel;
class Queue extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{
    protected function _construct()
    {
        $this->_init('pss_crm_queue','pss_crm_queue_id');
    }
}
