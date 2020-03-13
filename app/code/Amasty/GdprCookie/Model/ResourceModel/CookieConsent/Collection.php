<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2019 Amasty (https://www.amasty.com)
 * @package Amasty_GdprCookie
 */


namespace Amasty\GdprCookie\Model\ResourceModel\CookieConsent;

use Amasty\GdprCookie\Model\CookieConsent;
use Amasty\GdprCookie\Model\ResourceModel\CookieConsent as CookieConsentResource;
use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;

/**
 * @method CookieConsent[] getItems()
 */
class Collection extends AbstractCollection
{
    /**
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function _construct()
    {
        $this->_init(CookieConsent::class, CookieConsentResource::class);
        $this->_setIdFieldName($this->getResource()->getIdFieldName());
    }

    public function joinCustomerData()
    {
        $this->getSelect()->joinLeft(
            ['customer' => $this->getTable('customer_entity')],
            'customer.entity_id = main_table.customer_id',
            [
                'email',
                'name' => new \Zend_Db_Expr("CONCAT_WS(' ', prefix, firstname, middlename, lastname, suffix)")
            ]
        );

        return $this;
    }
}
