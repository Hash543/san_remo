<?php
/**
 * @author Cristian Sanclemente <csanclemente@alfa9.com>
 * @copyright Copyright (c) 2017 Alfa9 (http://www.alfa9.com)
 * @package Alfa9
 */

namespace PSS\CRM\Model;
class Queue extends \Magento\Framework\Model\AbstractModel implements \PSS\CRM\Api\Data\QueueInterface, \Magento\Framework\DataObject\IdentityInterface
{
    const CACHE_TAG = 'pss_crm_queue';

    protected function _construct()
    {
        $this->_init('PSS\CRM\Model\ResourceModel\Queue');
    }

    public function getIdentities()
    {
        return [self::CACHE_TAG . '_' . $this->getId()];
    }
}
