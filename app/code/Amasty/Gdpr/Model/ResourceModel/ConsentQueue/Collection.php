<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2019 Amasty (https://www.amasty.com)
 * @package Amasty_Gdpr
 */


namespace Amasty\Gdpr\Model\ResourceModel\ConsentQueue;

use Amasty\Gdpr\Model\ConsentQueue;

/**
 * @method ConsentQueue[] getItems()
 */
class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{
    /**
     * @SuppressWarnings(PHPMD.CamelCaseMethodName)
     */
    public function _construct()
    {
        parent::_construct();
        $this->_init(
            \Amasty\Gdpr\Model\ConsentQueue::class,
            \Amasty\Gdpr\Model\ResourceModel\ConsentQueue::class
        );
        $this->_setIdFieldName($this->getResource()->getIdFieldName());
    }

    /**
     * @param $customerIds
     */
    public function insertIds($customerIds)
    {
        $this->addFieldToFilter(
            'status',
            ['eq' => ConsentQueue::STATUS_PENDING]
        );

        if ($this->getSize() == 0) {
            $this->getConnection()->truncateTable($this->getMainTable());
        }

        $data = [];
        foreach ($customerIds as $id) {
            $data[] = ['customer_id' => $id, 'status' => ConsentQueue::STATUS_PENDING];
        }

        $this->getConnection()->insertOnDuplicate(
            $this->getMainTable(),
            $data,
            ['status']
        );
    }

    public function addStatusFilter($status)
    {
        $this->addFieldToFilter('status', $status);
    }
}
