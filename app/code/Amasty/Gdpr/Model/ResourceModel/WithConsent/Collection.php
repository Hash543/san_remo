<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2019 Amasty (https://www.amasty.com)
 * @package Amasty_Gdpr
 */


namespace Amasty\Gdpr\Model\ResourceModel\WithConsent;

class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{
    /**
     * @SuppressWarnings(PHPMD.CamelCaseMethodName)
     */
    public function _construct()
    {
        parent::_construct();
        $this->_init(
            \Amasty\Gdpr\Model\WithConsent::class,
            \Amasty\Gdpr\Model\ResourceModel\WithConsent::class
        );
        $this->_setIdFieldName($this->getResource()->getIdFieldName());
    }

    public function getConsentCustomerIds()
    {
        //@codingStandardsIgnoreStart
        $this->getSelect()->group('customer_id');
        //@codingStandardsIgnoreStop
        $customerIds = $this->getColumnValues('customer_id');

        return $customerIds;
    }
}
