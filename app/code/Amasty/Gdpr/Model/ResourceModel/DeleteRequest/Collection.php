<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2019 Amasty (https://www.amasty.com)
 * @package Amasty_Gdpr
 */


namespace Amasty\Gdpr\Model\ResourceModel\DeleteRequest;

use Amasty\Gdpr\Model\DeleteRequest;
use Amasty\Gdpr\Model\ResourceModel\DeleteRequest as DeleteRequestResource;
use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;

class Collection extends AbstractCollection
{
    /**
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function _construct()
    {
        parent::_construct();

        $this->_init(DeleteRequest::class, DeleteRequestResource::class);
        $this->_setIdFieldName($this->getResource()->getIdFieldName());
    }

    /**
     * @param $customerId
     */
    public function deleteByCustomerId($customerId)
    {
        $this->getConnection()->delete(
            $this->getMainTable(),
            ['customer_id = ?' => $customerId]
        );
    }
}
